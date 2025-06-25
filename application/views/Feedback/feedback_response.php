<?php $this->load->view('Layouts/header') ?>
<?php
$url = $this->uri->segment(1);
$hold_date = date('Y-m-d h:i:s', strtotime(timestamp . ' + 2 days'));
?>
<div class="width-my">
    <div class="container-fluid">
        <div class="taskPageSize taskPageSizeDashboard" style="height:auto !important;">
            <div class="alertMessage">
                <div class="alert alert-dismissible alert-success msg">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Thanks!</strong>
                    <a href="#" class="alert-link">Add Successfully</a>
                </div>
                <div class="alert alert-dismissible alert-danger err">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Failed!</strong>
                    <a href="#" class="alert-link">Try Again.</a>
                </div>
            </div>
            <div class="row default-page-height">
                <div class="col-md-8 col-sm-offset-2">
                    <div class="tab" role="tabpanel">
                        <input type="hidden" name="lead_id" id="lead_id" value="<?= $leadDetails->lead_id ?>" readonly>
                        <input type="hidden" name="user_id" id="user_id" value="<?= $_SESSION['isUserSession']['user_id'] ?>" readonly>
                        <ul class="nav nav-tabs" role="tablist">

                            <li role="presentation" class="borderList active"><a href="#FeedbackSaction" aria-controls="Feedback" role="tab" data-toggle="tab">Feedback</a></li>

                        </ul><hr> 
                        <div class="tab-content tabs">
                            <div role="tabpanel" class="tab-pane fade in active" id="FeedbackSaction">
                                <div class="login-formmea">
                                    <div class="box-widget widget-module">
                                        <div class="widget-container">
                                            <div class=" widget-block">
                                                <div class="row">
                                                    <h4>Feedback Response</h4> <hr>
                                                    <table class="table table-bordered table-hover table-striped">
                                                        <tbody>
                                                            <tr>
                                                                <th><b>Customer Name </b></th>
                                                                <td><?= ($feedback['name'] ? $feedback['name'] : "-") ?></td>
                                                                <th><b>Customer Email </b></th>
                                                                <td><?= ($feedback['email'] ? $feedback['email'] : "-") ?></td>
                                                            </tr>

                                                            <tr>
                                                                <th><b>Customer Mobile </b></th>
                                                                <td colspan="3"><?= ($feedback['mobile'] ? $feedback['mobile'] : "-") ?></td>
                                                            </tr>
                                                            <?php
                                                            $i = 1;
                                                            foreach ($feedback['data'] as $row) {
                                                                ?>
                                                                <tr>
                                                                    <th colspan="4"><b>Question <?= $i ?> : <?= $row['question'] ?></b></th>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="4"><b>Answer : </b><?= $row['answer'] ?></td>
                                                                </tr>
                                                                <?php
                                                                $i++;
                                                            }
                                                            ?>
                                                            <tr>
                                                                <th colspan="4"><b>Remarks : </b></th>
                                                            </tr>
                                                            <tr>
                                                                <th colspan="4"><?= $feedback['remarks'] ?></th>
                                                            </tr>
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
            </div>
        </div> 
    </div>
</div>
</div>

<?php $this->load->view('Layouts/footer') ?>
<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
<script>
    var csrf_token = $("input[name=csrf_token]").val();
</script>
