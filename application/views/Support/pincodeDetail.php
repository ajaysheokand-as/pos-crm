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
                                                    <h4>Pincode Detail </h4>
                                                    
                                                     <form method="POST" class="form-inline" style="margin-top:8px;" action="<?= base_url('support/Searchpincode'); ?>">
                                                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="master_pincode" value="<?= !empty($_POST['filter_input']) ? $_POST['filter_input'] : '' ?>" placeholder="Enter search keywords." required/>
                                                        </div>
                                                       
                                                        <div class="form-group">
                                                            <select class="form-control" id="category_id" name="m_city_name" >
                                                                 <option value="" disabled selected>Select</option>
                                                                    <?php foreach ($city_data1 as $city_data_row1) { ?>
                                                                        <option value="<?= $city_data_row1['m_pincode_city_id'] ?>"><?= $city_data_row1['m_city_name'] ?></option>
                                                                    <?php } ?>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Search</button> <button  type="button" onclick="location.href = '<?= base_url('blog-list') ?>'" class="btn btn-outline-light">Reset</button>
                                                        <a class="btn btn-primary" href="<?= base_url('support/add-pincode') ?>" role="button">ADD Pincode</a>
                                                    </form>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">
                                                       
                                                    <table class="table dt-table1 table-striped table-bordered table-responsive table-hover" style="border: 1px solid #dde2eb">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="whitespace"><b>City Name</b></th>
                                                                          <!--  <th class="whitespace"><b>City ID</b></th>  -->
                                                                            <th class="whitespace"><b>Pincode</b></th>
                                                                         
                                                                        </tr>
                                                                    </thead>
                                                                    
                                                                    
                                                                       <tbody>
                                                                             <?php if (!empty($pincode_details)): ?>
                                                                   
                                                                       <?php foreach ($pincode_details as $detail): ?> 
                                                                                <tr class="table-default" id="id_<?=$i?>">
                                                                                    <td class="whitespace"><?php echo htmlspecialchars($detail->m_pincode_value); ?></td> 
                                                                                    <td class="whitespace"><?php echo htmlspecialchars($detail->m_city_name); ?></td>
                                                                                                                                                                    
                                                                                  
                                                                                </tr>
                                                                        
                                                                               <?php endforeach; ?>

                                                            <?php else: ?>
                                                            <p>No details found for this pincode.</p>
                                                        <?php endif; ?>
                                                                    </tbody>
                                                                  
                                                                </table>
                                                                
                                                        </form>
                                                        
                                                       
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <?= $links; ?> 
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

