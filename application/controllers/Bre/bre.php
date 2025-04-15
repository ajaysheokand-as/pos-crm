

<div class="tab-content tabs">
    <div role="tabpanel" class="tab-pane fade in active" id="LeadBREResult">                   

        <div id="LeadBREResultInner">

            <?php if (in_array($leadDetails->lead_status_id, array(5, 6, 11)) && ((agent == 'CR2' && $leadDetails->lead_credit_assign_user_id == user_id) || agent == 'CA') && $leadDetails->customer_bre_run_flag==0) { ?>
                <p>BRE Requirement  : Please ensure all the data points and apis has been called before run the BRE. If not then you will get the rejection and repetition of work leads to lower productivity.
                    <br/><button onclick="call_bre_rule_engine()" class="btn btn-success lead-sanction-button">RUN BRE</button></p>
            <?php } else if (in_array($leadDetails->lead_status_id, array(5, 6, 11)) && ((agent == 'CR2' && $leadDetails->lead_credit_assign_user_id == user_id) || agent == 'CA') && $leadDetails->customer_bre_run_flag==1) { ?>
                <p>BRE Requirement  : Please ensure all the data points and apis has been called before run the BRE. If not then you will get the rejection and repetition of work leads to lower productivity.
                    <br/><button onclick="call_bre_edit_application()" class="btn btn-success lead-sanction-button" style="width:auto;padding:10px !important">Edit Application</button></p>
              <?php
            }
              ?>
            <div class="bre_result_container" id="bre_rule_result_container"> 


            </div>
        </div>
    </div>
</div>
