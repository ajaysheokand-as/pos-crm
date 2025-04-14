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

                                                        <form id="leadIddata" autocomplete="off" action="<?=base_url('support/searchDocsId'); ?>" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control" name="lead_id" id="lead_id" required="" value="<?php if($docsInfo['lcr_lead_id']!=''){echo $docsInfo['lcr_lead_id'];}else{ echo $_POST['lead_id'];} ?>" placeholder="Please enter lead id*" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
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
                                        <?php if(!empty($status)){ ?>
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
                                                                            <th class="whitespace"><b>Docs Id</b></th>                              
                                                                            <th class="whitespace"><b>Pancard</b></th>
                                                                            <th class="whitespace"><b>Mobile</b></th>
                                                                            <th class="whitespace"><b>Docs Type</b></th>
                                                                            <th class="whitespace"><b>Sub Docs Type</b></th>
                                                                            <th class="whitespace"><b>Password</b></th>
                                                                            <th class="whitespace"><b>Action</b></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php if(count($leadInfo)>0){ $i=1; foreach($leadInfo as $key => $value) { ?>
                                                                        <tr id="id_<?=$i?>">                                                                            
                                                                            <td class="whitespace data-fixed-columns"><?=$i?></td> 
                                                                            <td class="whitespace"><?=$value['docs_id']?></td>                            
                                                                            <td class="whitespace"><?=$value['pancard']?></td>
                                                                            <td class="whitespace"><?=$value['mobile']?></td>
                                                                            <td class="whitespace"><?=$value['docs_type']?></td>
                                                                            <td class="whitespace"><?=$value['sub_docs_type']?></td>
                                                                            <td class="whitespace"><?=(!empty($value['pwd'])?$value['pwd']:'-')?></td>
                                                                           
                                                                           
                                                                            <td class="whitespace"><a href="<?=base_url('upload/'.$value['file']); ?>" target="_blank"><i class="fa fa-eye" style="padding : 3px; color : #35b7c4; border : 1px solid #35b7c4;"></i></a> <a class="docs_delete_class" title="Delete" href="javascript:void();" onclick="docsDelete('<?=$this->encrypt->encode($value['docs_id']) ?>','id_<?=$i?>');"><i class="fa fa-trash" aria-hidden="true" style="padding : 3px; color : #35b7c4; border : 1px solid #35b7c4;"></i></a></td>                                                                   
                                                                       
                                                                        </tr> 
                                                                        <?php $i++;}} else { ?>
                                                                           <tr><td colspan="5" class="whitespace" style="color:red;">Data not found.</td></tr>
                                                                        <?php } ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Update Docs Details </h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">                                                        
                                                        <div class="row">
                                                        <form autocomplete="off" action="" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <input type="hidden" name="lead_id" id="lead_id" value="<?php if(isset($leadInfo[0]['lead_id']) && $leadInfo[0]['lead_id']!=''){echo $leadInfo[0]['lead_id'];}?>" />                                                          
                                                            <div class="row">                                                                           
                                                                <div class="col-md-6"> 
                                                                    <label for="lead_followup_remark">Lead Followup Remark <span class="span" style="color:red;">*</span></label>                            
                                                                    <textarea class="form-control" name="lead_followup_remark" required id="lead_followup_remark" style="width:100% !important;height:50px !important;" autocomplete="off" placeholder="Please enter lead followup remark."></textarea>
                                                                </div>                                                                                                          
                                                            </div>
                                                            <div class="row">                                                                
                                                                <div class="col-md-6">
                                                                    <button type="button" class="button-add btn update_docs_details">Update Docs Details</button>
                                                                </div>
                                                            </div>

                                                        </form>                                                          
                                                        </div>                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>
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

