<?php
foreach ($master_bre_category as $bre_category_data) {
    $bre_category_id = $bre_category_data['m_bre_cat_id'];
    $bre_category_name = $bre_category_data['m_bre_cat_name'];
    ?>
    <details class = "bre_result_category">

        <summary class = "bre_result_category_name"><span><?= $bre_category_name ?></span></summary> 
        <div class="content">
            <table>
                <thead>
                    <tr>
                        <th>Rule Description</th>
                        <th>Cut Off Value</th>
                        <th>Actual Value</th>
                        <th>Relevant inputs</th>
                        <th>System Decision</th>
                        <th>Manual Decision</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($bre_rule_result as $bre_data) {
                        $bre_rule_category_id = $bre_data['m_bre_cat_id'];
                        $bre_rule_trans_id = $bre_data['lbrr_id'];
                        $bre_rule_name = $bre_data['lbrr_rule_name'];
                        $bre_rule_cutoff_value = $bre_data['lbrr_rule_cutoff_value'];
                        $bre_rule_actual_value = json_decode($bre_data['lbrr_rule_actual_value'], true);
                        $bre_rule_relevant_inputs = json_decode($bre_data['lbrr_rule_relevant_inputs'], true);
                        $bre_rule_system_decision_id = $bre_data['lbrr_rule_system_decision_id'];
                        $bre_rule_manual_decision_id = $bre_data['lbrr_rule_manual_decision_id'];
                        $bre_rule_manual_decision_remarks = $bre_data['lbrr_rule_manual_decision_remarks'];

                        if ($bre_rule_category_id == $bre_category_id) {
                            ?>

                            <tr>
                                <td><?= $bre_rule_name ?></td>
                                <td><?= $bre_rule_cutoff_value ?></td>
                                <td><?php
                                    if (is_array($bre_rule_actual_value)) {
                                        foreach ($bre_rule_actual_value as $actual_key => $actual_value) {
                                            if (!empty($actual_key)) {
                                                ?>
                                                <p><?= $actual_key ?> : <?= $actual_value ?></p>
                                                <?php
                                            } else {
                                                echo $actual_value;
                                            }
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="main_mdl1">
                                    <?php
                                    if (!empty($bre_rule_relevant_inputs)) {

                                        foreach ($bre_rule_relevant_inputs as $actual_key => $actual_value) {
                                            ?>
                                            <p><?= $actual_key ?> : <?= $actual_value ?></p>
                                            <?php
                                        }
                                    }
                                    ?>
                                </td>

                                <td class="bre_td_system_decision">
                                    <?php if ($bre_rule_system_decision_id == 1) { ?>
                                        <p>
                                            <span>Approved</span>
                                            <span><img src="<?= base_url(); ?>/public/front/img/tick.png"></span>
                                        </p>
                                    <?php } else if ($bre_rule_system_decision_id == 2) { ?>
                                        <p>
                                            <span>Refer</span>  
                                            <span><img src="<?= base_url(); ?>/public/front/img/refer.png"></span>
                                        </p>
                                    <?php } else if ($bre_rule_system_decision_id == 3) { ?>
                                        <p>
                                            <span>Rejected</span>  
                                            <span><img src="<?= base_url(); ?>/public/front/img/reject.png"></span>
                                        </p>
                                    <?php } else { ?>
                                        <p>Not Applicable</p>
                                        <?php
                                    }
                                    ?></td>
                                <td class="bre_td_manual_decision">
                                    <?php if ($bre_rule_manual_decision_id == 1) { ?>
                                        <p>
                                            <span>Approved</span>
                                            <?php if (false && $bre_rule_system_decision_id == 2 && $bre_rule_manual_decision_id == 1) { ?>
                                                <span><?= $bre_rule_manual_decision_remarks; ?></span>
                                            <?php } ?>
                                            <span title="<?= ($bre_rule_system_decision_id == 2 && $bre_rule_manual_decision_id == 1) ? $bre_rule_manual_decision_remarks : "" ?>"><img src="<?= base_url(); ?>/public/front/img/tick.png"></span>
                                        </p>
                                    <?php } else if ($bre_rule_manual_decision_id == 2) { ?>
                                        <p>
                                            <span>Refer</span>
                                            <span><img src="<?= base_url(); ?>/public/front/img/refer.png"></span>
                                        </p>
                                        <p> 
                                            <select id="deviation_action_<?= $bre_rule_trans_id ?>" onchange="show_bre_deviation_box(<?= $bre_rule_trans_id ?>)">
                                                <option value="">Select Action</option>
                                                <option value="1">Approve</option>
                                                <option value="3">Reject</option>
                                            </select>
                                            <textarea class="hide" id="deviation_remark_<?= $bre_rule_trans_id ?>" rows="5" cols="10"></textarea>
                                            <button class="hide btn btn-danger" id="deviation_btn_<?= $bre_rule_trans_id ?>" onclick="save_bre_rule_result_deviation(<?= $bre_rule_trans_id ?>)">Save</button>
                                        </p>
                                    <?php } else if ($bre_rule_manual_decision_id == 3) { ?>
                                        <p>
                                            <span>Rejected</span>  
                                            <?php if (false && $bre_rule_system_decision_id == 2 && $bre_rule_manual_decision_id == 3) { ?>
                                                <span><?= $bre_rule_manual_decision_remarks; ?></span>
                                            <?php } ?>
                                            <span title="<?= ($bre_rule_system_decision_id == 2 && $bre_rule_manual_decision_id == 3) ? $bre_rule_manual_decision_remarks : "" ?>"><img src="<?= base_url(); ?>/public/front/img/reject.png"></span>
                                        </p>
                                    <?php } else {
                                        ?>
                                        <p>Not Applicable</p>
                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table> 
        </div> 
    </details>
<?php } ?>
