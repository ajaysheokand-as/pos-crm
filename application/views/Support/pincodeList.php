<section class="parent_wrapper">
<?php
$this->load->view('Layouts/header');
$uri = $this->uri->segment(1);
$pagination_links = "";
include('inner_layout.php');
?>
<style>
        .pagination {
            display: inline-block;
            padding-left: 0;
            margin: 20px 0;
            border-radius: 4px;
        }
        ul li {
            padding: 20px 0px 20px 0px;
        }
        .pagination > li {
            display: inline-block !important;
        }
        .pagination > li:first-child > a, .pagination > li:first-child > span {
            margin-left: 0;
            border-bottom-left-radius: 4px;
            border-top-left-radius: 4px;
        }
        .pagination li a {
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid #ccc;
            color: #333;
        }
        .pagination li strong {
            padding: 5px 10px;
            background-color: #333;
            color: #fff;
            border: 1px solid #333;
        }
        .pagination > li > a, .pagination > li > span {
            position: relative;
            float: left;
            padding: 6px 6px  !important;
            line-height: 1.42857143;
            text-decoration: none;
            color: #337ab7;
            background-color: #ffffff;
            border: 0px solid #dddddd !important; 
            margin-left: -1px;
        }
    </style>
    <div class="container-fluid">
        <div class="taskPageSize taskPageSizeDashboard">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-container list-menu-view">
                        <div class="page-content">
                            <div class="main-container">
                                <div class="container-fluid">
                                    <div class="col-md-2 drop-me">
                                        <?php $this->load->view('Layouts/leftsidebar') ?>
                                    </div>
                                    <div class="col-sm-12 div-right-sidebar">
                                        <div class="login-formmea">

                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <span class="inner-page-tag">Pincode List</span>
                                                    <form method="POST" class="form-inline" style="margin-top:8px;" action="<?= base_url('support/Searchpincode'); ?>">
                                                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="master_pincode" value="<?= !empty($_POST['filter_input']) ? $_POST['filter_input'] : '' ?>" placeholder="Enter search keywords." required/>
                                                        </div>
                                                       
                                                        <div class="form-group">
                                                            <select class="form-control" id="category_id" name="m_city_name">
                                                                 <option value="" disabled selected>Select</option>
                                                                    <?php foreach ($city_data as $city_data_row1) { ?>
                                                                        <option value="<?= $city_data_row1['m_pincode_city_id'] ?>"><?= $city_data_row1['m_city_name'] ?></option>
                                                                    <?php } ?>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Search</button> <button  type="button" onclick="location.href = '<?= base_url('blog-list') ?>'" class="btn btn-outline-light">Reset</button>
                                                        <a class="btn btn-primary" href="<?= base_url('support/add-pincode') ?>" role="button">ADD Pincode</a>
                                                    </form>
                                         
                                                    
                                                    <div id="resultContainer"></div>

                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">   
    
                                                        <div class="row">
                                                            <?php if (!empty($this->session->flashdata('success_msg'))) { ?>
                                                                <div class="alert alert-success alert-dismissible">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <?= $this->session->flashdata('success_msg'); ?>
                                                                </div>
                                                            <?php } else if (!empty($this->session->flashdata('errors_msg'))) { ?>
                                                                <div class="alert alert-danger alert-dismissible">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <?= $this->session->flashdata('errors_msg'); ?>
                                                                </div>
                                                            <?php } ?>
                                                            <div class="scroll_on_x_axis">
                                                                <table class="table dt-table1 table-striped table-bordered table-responsive table-hover" style="border: 1px solid #dde2eb">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="whitespace"><b>City Name</b></th>
                                                                            <th class="whitespace"><b>City ID</b></th>
                                                                            <th class="whitespace"><b>Pincode</b></th>
                                                                            <th class="whitespace"><b>Action</b></th>
                                                                        </tr>
                                                                    </thead>
                                                                    
                                                                    
                                                                       <tbody>
                                                                   
                                                                        <?php foreach ($city_data as $city_data_row) { ?>
                                                                                <tr class="table-default" id="id_<?=$i?>">
                                                                                    <td class="whitespace"><?= $city_data_row['m_city_name'] ?></td> 
                                                                                    <td class="whitespace"><?= $city_data_row['m_city_id'] ?></td>
                                                                                    <td class="whitespace"><?= $city_data_row['m_pincode_value'] ?></td>                                                                                   
                                                                                                                                                   
                                                                                    <td class="whitespace">
                                                                                        <a class="btn btn-primary btn-sm" title="Delete" href="javascript:void();" onclick="blogDelete('<?=$this->encrypt->encode($pincodeData["wb_id"]) ?>','id_<?=$i?>');"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                                                                    </td>
                                                                                </tr>
                                                                        
                                                                        <?php } ?>
                                                                    
                                                                        
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
</section>
<?php $this->load->view('Layouts/footer'); ?>
<?php $this->load->view('Support/support_js'); ?>
</section>
</section>



