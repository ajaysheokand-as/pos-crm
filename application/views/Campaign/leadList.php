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
                width: 165px;
            }
        </style>
        <section>
            <div class="logo_container">
                <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo"> <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
            </div>
            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="login-formmea">
                        <div class="box-widget widget-module">
                            <div class="widget-head clearfix">
                                <span class="h-icon"><i class="fa fa-th"></i></span>
                                <span class="inner-page-tag">Leads </span>
                            </div>
                            <div class="widget-container">
                                <div class=" widget-block">
                                    <div class="row">
                                        <div class="table-responsive">
                                            <!-- data-order='[[ 0, "desc" ]]'  dt-table -->
                                            <table class="table table-hover" id="domainTable">
                                                <thead>
                                                    <tr>
                                                        <th class="whitespace data-fixed-columns"><b>Lead ID</b></th>
                                                        <th class="whitespace"><b>Applied&nbsp;On</b></th>
                                                        <th class="whitespace"><b>First Name</b></th>
                                                        <th class="whitespace"><b>Last Name</b></th>
                                                        <th class="whitespace"><b>Mobile No.</b></th>
                                                        <th class="whitespace"><b>Age</b></th>
                                                        <th class="whitespace"><b>Employment Name</b></th>
                                                        <th class="whitespace"><b>Employment Type</b></th>
                                                        <th class="whitespace"><b>Current Salary</b></th>
                                                        <th class="whitespace"><b>Current EMI Per Month</b></th>
                                                        <th class="whitespace"><b>PAN</b></th>
                                                        <th class="whitespace"><b>Email</b></th>
                                                        <th class="whitespace"><b>City</b></th>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if ($campaignData->num_rows() > 0) {
                                                        $sn = 1;
                                                        foreach ($campaignData->result() as $row) {
                                                            echo '<tr>
                                                                <td>' . (!empty($row->lead_id) ? $row->lead_id : '-') . '</td>
                                                                <td>-</td>
                                                                <td>' . (!empty($row->first_name) ? $row->first_name : '-') . '</td>
                                                                <td>' . (!empty($row->last_name) ? $row->last_name : '-') . '</td>
                                                                <td>' . (!empty($row->phone_number) ? $row->phone_number : '-') . '</td>
                                                                <td>' . (!empty($row->age) ? $row->age : '-') . '</td>
                                                                <td>' . (!empty($row->employer_name) ? $row->employer_name : '-') . '</td>
                                                                <td>' . (!empty($row->employment_type) ? $row->employment_type : '-') . '</td>
                                                                <td>' . (!empty($row->current_salary) ? $row->current_salary : '-') . '</td>
                                                                <td>' . (!empty($row->current_emi_per_month) ? $row->current_emi_per_month : '-') . '</td>
                                                                <td>' . (!empty($row->pan_number) ? $row->pan_number : '-') . '</td>
                                                                <td>' . (!empty($row->email) ? $row->email : '-') . '</td>
                                                                <td>' . (!empty($row->city) ? $row->city : '-') . '</td>
                                                            </tr>';
                                                        }
                                                    
                                                    } else {
                                                        ?>
                                                        <tr>
                                                            <th colspan="15" class="whitespace data-fixed text-center"><b style="color: #b73232;">No Record Found...</b></th>
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
        </section>