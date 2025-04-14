<div id="sidenav1">
    <style>
        .list-group li {
            padding: 5px 10px !important;
        }
        
        .panel-heading h4 i {
            margin-right: 5px;
            margin-left: 2px;
        }
        
        .panel-heading h4 .caret {
            margin-left: 2px;
        }
    </style>
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#sideNavbar"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
    </div>
    <div class="navbar-collapse" id="sideNavbar" style="width: 100%; padding: 0px !important;">
        <div class="panel-group" id="accordion">
            <?php if (agent == 'CA') { ?>
                    <div class="panel panel-default" >
                        <div class="panel-heading"> 
                            <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree"> <i class="fa fa-sign-in"></i>&nbsp;Users<span class="caret"></span></a> </h4>
                        </div>

                        <div id="collapseThree" class="panel-collapse collapse"> 
                            <ul class="list-group"> 
                                <li class="navlink2"><a href="<?= base_url('ums/add-user') ?>"><i class="fa-solid fa-angles-right"></i> Add User</a></li>
                                <li class="navlink2"><a href="<?= base_url('ums') ?>"><i class="fa-solid fa-angles-right"></i> View User</a></li>
                            </ul>
                        </div>
                    </div>
                <?php } ?>
            <?php if (agent == 'CA') { ?>
                    <div class="panel panel-default" style="border: solid 1px #ddd;">
                        <div class="panel-heading">
                            <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapseimport"><i class="fa fa-table"></i> Import Data<span class="caret"></span></a> </h4>
                        </div>
                        <div id="collapseimport" class="panel-collapse collapse">
                            <ul class="list-group">
                                <li><a href="<?= base_url('ViewImportData') ?>" class="navlink"><i class="fa fa-angle-right"></i>Import CSV</a></li>
                            </ul>
                        </div>
                    </div>
                <?php } ?>

            <?php if (agent == 'CA' || $user->isAddNewBankMaster == 1) { ?>
                    <div class="panel panel-default" style="border: solid 1px #ddd;">
                        <div class="panel-heading">
                            <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"><i class="fa fa-table"></i> Add Bank Details<span class="caret"></span></a> </h4>
                        </div>
                        <div id="collapseTwo" class="panel-collapse collapse">
                            <ul class="list-group">
                                <li><a href="<?= base_url('addBankDetails') ?>" class="navlink"><i class="fa fa-angle-right"></i> Add Bank Lists</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="panel panel-default" style="border: solid 1px #ddd;">
                        <div class="panel-heading">
                            <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour"><i class="fa fa-table"></i> Add Holiday Details<span class="caret"></span></a> </h4>
                        </div>
                        <div id="collapseFour" class="panel-collapse collapse">
                            <ul class="list-group">
                                <li><a href="<?= base_url('addHolidayDetails') ?>" class="navlink"><i class="fa fa-angle-right"></i> Add Holiday Lists</a></li>
                            </ul>
                        </div>
                    </div>
                <?php } else if (agent == 'CA') { ?>

                    <div class="panel panel-default" style="border: solid 1px #ddd;">
                        <div class="panel-heading">
                            <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapse3"><i class="fa fa-table"></i>&nbsp;Client Company Details<span class="caret"></span></a> </h4>
                        </div>
                        <div id="collapse3" class="panel-collapse collapse">
                            <ul class="list-group">
                                <li><a href="<?= base_url('addCompanyDetails'); ?>" class="navlink"><i class="fa fa-angle-right"></i>&nbsp;Company Lists</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="panel panel-default" style="border: solid 1px #ddd;">
                        <div class="panel-heading">
                            <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapse4"><i class="fa fa-table"></i>&nbsp;Dashboard<span class="caret"></span></a> </h4>
                        </div>
                        <div id="collapse4" class="panel-collapse collapse">
                            <ul class="list-group">
                                <li><a href="<?= base_url('adminViewDashboard'); ?>" class="navlink"><i class="fa fa-angle-right"></i>&nbsp;Add Menus</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="menu-hide">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a href=""><span class="glyphicon glyphicon-new-window"></span>Add Company</a> 
                                </h4>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a href=""><span class="glyphicon glyphicon-new-window"></span>External Link</a> </h4>
                            </div>
                        </div>
                    </div>
                <?php } ?>
        </div>
    </div>
</div>
