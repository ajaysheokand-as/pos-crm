<?php 
  foreach ($finbox_details as $key => $value) {
    $finbox[$value['name']] = $value['value'];
}

if($finbox_details>0) {
$return_data = '<div class="table-responsive">
                            <h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;"> FinBox Features
                                <i class="fa fa-angle-double-down" style="user-select: auto;"></i>
                            </h4>
                            <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>                
                                    <th class="whitespace">FIS&nbsp;v1</th>                            
                                    <th class="whitespace">FIS&nbsp;v2</th>      
                                    <th class="whitespace">FIS&nbsp;Digital&nbsp;202204&nbsp;probability</th>      
                                    <th class="whitespace">FIS&nbsp;SMS&nbsp;pillar&nbsp;V1&nbsp;</th>      
                                    <th class="whitespace">FIS&nbsp;aPP&nbsp;pillar&nbsp;V2&nbsp;</th>  
                                    <th class="whitespace">FIS&nbsp;confidence</th>
                                    <th class="whitespace">FIS&nbsp;affordabhility&nb
                                    sp;V1&nbsp;</th>
                                    <th class="whitespace">FIS&nbsp;recommended&nbsp;due&nbsp;date&nbsp;</th>
                                    <th class="witespace">Digital&nbsp;svvinesfis_v1s</th>
                                </tr>
                                <tr>
                                    <td class="whitespace">' . (($finbox['fis_v1']) ? $finbox['fis_v1'] : '-') . '</td>
                                    <td class="whitespac">' . (($finbox['fis_v2']) ? $finbox['fis_v2'] : '-') . '</td>
                                    <td class="whitespace">' . (($finbox['fis_digital_202204_probability']) ? $finbox['fis_digital_202204_probability'] : '-') . '</td>
                                    <td class="whitespace">' . (($finbox['fis_sms_pillar_v1']) ? $finbox['fis_sms_pillar_v1'] : '-') . '</td>
                                    <td class="whitespace">' . (($finbox['fis_apps_pillar_v1']) ? $finbox['fis_apps_pillar_v1'] : '-') . '</td>
                                    <td class="whitespace">' . (($finbox['fis_confidence']) ? $finbox['fis_confidence'] : '-') . '</td>
                                    <td class="whitespace">' . (($finbox['fis_affordability_v1']) ? $finbox['fis_affordability_v1'] : '-') . '</td>
                                    <td class="whitespace">' . (($finbox['fis_recommended_due_date']) ? $finbox['fis_recommended_due_date'] : '-') . ' </td>
                                    <td class="whitespace">' . (($finbox['digital_savviness']) ? $finbox['digital_savviness'] : '-') . '</td>
                               </tr>
                               </tbody>
                               </table>
                            </div>
                               <div class="table-responsive">
                                    <h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Apps Features  
                                        <i class="fa fa-angle-double-down" style="user-select: auto;"></i>
                                    </h4>
                                    <table class="table table-bordered table-striped"><tbody><tr>                
                                    <th class="whitespace">Count&nbsp;apps</th>                            
                                    <th class="whitespace">Count&nbsp;paid&nbsp;apps</th>      
                                    <th class="whitespace">Count&nbsp;apps genre entertainment</th>      
                                    <th class="whitespace">Count apps genre finance</th>      
                                    <th class="whitespace">Count apps genre social</th>  
                                    <th class="whitespace">Count apps genre business</th>
                                    <th class="whitespace">Count apps genre communication </th>
                                    <th class="whitespace">Count apps sub segment digital lender c30</th>
                                    <th class="whitespace">Count apps genre finance_c30</th>
                                    <th class="whitespace">Count apps bad ratings_c30 </th>
                                    <th class="whitespace">Days since first install </th>
                               </tr><tr>
                                   <td class="whitespace">' . (($finbox['cnt_apps']) ? $finbox['cnt_apps'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['cnt_paid_apps']) ? $finbox['cnt_paid_apps'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['Count_apps_genre_entertainment']) ? $finbox['Count_apps_genre_entertainment'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['Count_apps_genre_finance']) ? $finbox['Count_apps_genre_finance'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['Count_apps_genre_social']) ? $finbox['Count_apps_genre_social'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['Count_apps_genre_business']) ? $finbox['Count_apps_genre_business'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['Count_apps_genre_communication']) ? $finbox['Count_apps_genre_communication'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['Count_apps_sub_segment_digital_lender_c30']) ? $finbox['Count_apps_sub_segment_digital_lender_c30'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['Count_apps_genre_finance_c30']) ? $finbox['Count_apps_genre_finance_c30'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['Count_apps_bad_ratings_c30']) ? $finbox['Count_apps_bad_ratings_c30'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['Days_since_first_install']) ? $finbox['Days_since_first_install'] : '-') . '</td>
                               </tr></tbody></table></div>
                               <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Device Features  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                   <th class="whitespace">Mobile model </th>                            
                                   <th class="whitespace">Brand </th>      
                                   <th class="whitespace">Available internal storage  </th>      
                                   <th class="whitespace">Total ram </th>      
                                   <th class="whitespace">Sdk device last active </th>  
                                   <th class="whitespace">Sdk device first active  </th>
                                   <th class="whitespace">SMS permission flag  </th>
                                   <th class="whitespace">Location permission flag  </th>
                                   <th class="whitespace">Phone state permission flag </th>
                                   <th class="whitespace">Unique devices </th>
                                   <th class="whitespace">Days since first install </th>

                               </tr><tr> 
                                   <td class="whitespace">' . (($finbox['mobile_model']) ? $finbox['mobile_model'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['brand']) ? $finbox['brand'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['available_internal_storage']) ? $finbox['available_internal_storage'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['total_ram']) ? $finbox['total_ram'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['sdk_device_last_active']) ? $finbox['sdk_device_last_active'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['sdk_device_first_active']) ? $finbox['sdk_device_first_active'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['sms_permission_flag']) ? $finbox['sms_permission_flag'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['location_permission_flag']) ? $finbox['location_permission_flag'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['phone_state_permission_flag']) ? $finbox['phone_state_permission_flag'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['unique_devices']) ? $finbox['unique_devices'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['Days_since_first_install']) ? $finbox['Days_since_first_install'] : '-') . '</td>
                               </tr></tbody></table></div>
                               
                               <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Froud Features  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                   <th class="whitespace">Indevice match flag </th>                            
                                   <th class="whitespace">Concentration location</th>      
                                   <th class="whitespace">Concentration flag   </th>      
                                   <th class="whitespace">Cnt fraud assists app </th>      
                                   <th class="whitespace">Cnt loan approval same client </th>  
                                   
                               </tr><tr>
                                   <td class="whitespace">' . (($finbox['indevice_match_flag']) ? $finbox['indevice_match_flag'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['concentration_location']) ? $finbox['concentration_location'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['concentration_location']) ? $finbox['concentration_location'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['concentration_flag']) ? $finbox['concentration_flag'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['cnt_loan_approval_same_client']) ? $finbox['cnt_loan_approval_same_client'] : '-') . '</td>
                                 
                               </tr></tbody></table></div>
                               
                               
                               <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Location Features  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                   <th class="whitespace">Latest location latitude </th>                            
                                   <th class="whitespace">Latest location longitude </th>      
                                  
                                   
                               </tr><tr>
                                   <td class="whitespace">' . (($finbox['latest_location_latitude']) ? $finbox['latest_location_latitude'] : '-') . '</td>
                                   <td class="whitespace">' . (($finbox['latest_location_longitude']) ? $finbox['latest_location_longitude'] : '-') . '</td>
                                  
                               </tr></tbody></table></div>

                               <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">SMS Features  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                               <table class="table table-bordered table-striped"><tbody><tr>                
                               <th class="whitespace">All sms count </th>                            
                               <th class="whitespace">Cnt savings accounts </th>      
                               <th class="whitespace">Auto debit bounce_m1 </th>      
                               <th class="whitespace">Acc0 amt debits c30 </th>      
                               <th class="whitespace">Acc0 amt debits p30   </th>  
                               <th class="whitespace">Acc0 avg bal  </th>
                               <th class="whitespace">Acc0 latest balance  </th>
                           </tr><tr>
                               <td class="whitespace">' . (($finbox['all_sms_count']) ? $finbox['all_sms_count'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['cnt_savings_accounts']) ? $finbox['cnt_savings_accounts'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['auto_debit_bounce_m1']) ? $finbox['auto_debit_bounce_m1'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['acc0_amt_debits_c30']) ? $finbox['acc0_amt_debits_c30'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['acc0_amt_debits_p30']) ? $finbox['acc0_amt_debits_p30'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['acc0_avg_bal']) ? $finbox['acc0_avg_bal'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['acc0_latest_balance']) ? $finbox['acc0_latest_balance'] : '-') . '</td>
                               </tr></tbody></table>
                               
                               <table class="table table-bordered table-striped"><tbody><tr>                
                               <th class="whitespace">Acc0 max bal 3 mo </th>                            
                               <th class="whitespace">Acc0 max bal c30    </th>      
                               <th class="whitespace">Acc0 max bal p30 </th>      
                               <th class="whitespace">Acc0 max balance </th>      
                               <th class="whitespace">Acc0 min bal 3 mo </th>  
                               <th class="whitespace">Acc0 min balance </th>
                             
                           </tr><tr>
                               <td class="whitespace">' . (($finbox['acc0_max_bal_3_mo']) ? $finbox['acc0_max_bal_3_mo'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['acc0_max_bal_c30']) ? $finbox['acc0_max_bal_c30'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['acc0_max_bal_p30']) ? $finbox['acc0_max_bal_p30'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['acc0_max_balance']) ? $finbox['acc0_max_balance'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['acc0_min_bal_3_mo']) ? $finbox['acc0_min_bal_3_mo'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['acc0_min_bal_3_mo']) ? $finbox['acc0_min_bal_3_mo'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['acc0_min_balance']) ? $finbox['acc0_min_balance'] : '-') . '</td>
                              
                               </tr></tbody></table>
                               
                               <table class="table table-bordered table-striped"><tbody><tr>                
                               <th class="whitespace">Acc0 recency  </th>                            
                               <th class="whitespace">Acc0 vintage </th>      
                               <th class="whitespace">Bounce flag </th>      
                               <th class="whitespace">Calculated income </th>      
                               <th class="whitespace">Calculated income confidence </th>  
                               <th class="whitespace">Calculated income source  </th>
                               <th class="whitespace">CC utilisation  </th>
                             
                           </tr><tr>
                              <td class="whitespace">' . (($finbox['acc0_recency']) ? $finbox['acc0_recency'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['acc0_vintage']) ? $finbox['acc0_vintage'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['bounce_flag']) ? $finbox['bounce_flag'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['calculated_income']) ? $finbox['calculated_income'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['calculated_income_confidence']) ? $finbox['calculated_income_confidence'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['calculated_income_source']) ? $finbox['calculated_income_source'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cc_utilisation']) ? $finbox['cc_utilisation'] : '-') . '</td>
                               </tr></tbody></table>

                               <table class="table table-bordered table-striped"><tbody><tr>                
                               <th class="whitespace">Cnt bounce senders c90 </th>                            
                               <th class="whitespace">Cnt delinquncy loan c30 </th>      
                               <th class="whitespace">Cnt delinquncy loan c60  </th>      
                               <th class="whitespace">Cnt loan rejected </th>      
                               <th class="whitespace">Cnt overdue senders c60 </th>  
                               <th class="whitespace">Cnt overdue senders c90   </th>
                               <th class="whitespace">Cnt overdue sms c30 </th>
                             
                            </tr><tr>
                              <td class="whitespace">' . (($finbox['cnt_bounce_senders_c90']) ? $finbox['cnt_bounce_senders_c90'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cnt_delinquncy_loan_c30']) ? $finbox['cnt_delinquncy_loan_c30'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cnt_delinquncy_loan_c60']) ? $finbox['cnt_delinquncy_loan_c60'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cnt_loan_rejected']) ? $finbox['cnt_loan_rejected'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cnt_overdue_senders_c60']) ? $finbox['cnt_overdue_senders_c60'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cnt_overdue_senders_c90']) ? $finbox['cnt_overdue_senders_c90'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cnt_overdue_sms_c30']) ? $finbox['cnt_overdue_sms_c30'] : '-') . '</td>
                            </tr></tbody></table>

                                <table class="table table-bordered table-striped"><tbody><tr>                
                               <th class="whitespace">Cnt overdue sms c60  </th>                            
                               <th class="whitespace">Cnt overdue sms c90 </th>      
                               <th class="whitespace">Cnt salary txns </th>      
                               <th class="whitespace">Cnt sms communicationtype_overduenotifications_c60 </th>      
                               <th class="whitespace">Cnt sms industrytype banking c30 </th>  
                               <th class="whitespace">Cnt sms industrytype digitallender c30 </th>
                               
                             
                            </tr>
                         <tr>
                            <td class="whitespace">' . (($finbox['cnt_overdue_sms_c60']) ? $finbox['cnt_overdue_sms_c60'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['cnt_overdue_sms_c90']) ? $finbox['cnt_overdue_sms_c90'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['cnt_salary_txns']) ? $finbox['cnt_salary_txns'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['cnt_sms_communicationtype_overduenotifications_c60']) ? $finbox['cnt_sms_communicationtype_overduenotifications_c60'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['cnt_sms_industrytype_banking_c30']) ? $finbox['cnt_sms_industrytype_banking_c30'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['cnt_sms_industrytype_digitallender_c30']) ? $finbox['cnt_sms_industrytype_digitallender_c30'] : '-') . '</td>
                          </tr></tbody></table>

                            <table class="table table-bordered table-striped"><tbody><tr>                
                               <th class="whitespace">Cnt sms industrytype insurance c30  </th>
                               <th class="whitespace">Cnt sms industrytype lending c30 </th>      
                               <th class="whitespace">Cnt sms industrytype lending c60  </th>  
                               <th class="whitespace">Cnt sms organisationtype bank c30    </th>
                               <th class="whitespace">Cnt sms organisationtype financialservicesprovider c60     </th>
                               <th class="whitespace">Cnt sms organisationtype governmentservice c30     </th>
                            </tr><tr>
                            <td class="whitespace">' . (($finbox['cnt_sms_industrytype_insurance_c30']) ? $finbox['cnt_sms_industrytype_insurance_c30'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['cnt_sms_industrytype_lending_c30']) ? $finbox['cnt_sms_industrytype_lending_c30'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['cnt_sms_industrytype_lending_c60']) ? $finbox['cnt_sms_industrytype_lending_c60'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['cnt_sms_organisationtype_bank_c30']) ? $finbox['cnt_sms_organisationtype_bank_c30'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['cnt_sms_organisationtype_financialservicesprovider_c60']) ? $finbox['cnt_sms_organisationtype_financialservicesprovider_c60'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['cnt_sms_organisationtype_governmentservices_c30']) ? $finbox['cnt_sms_organisationtype_governmentservices_c30'] : '-') . '</td>
                            
                                </tr></tbody></table>

                                <table class="table table-bordered table-striped"><tbody><tr>                
                               <th class="whitespace">Credit card user flag </th>
                               <th class="whitespace">Debit card user flag  </th>      
                               <th class="whitespace">External cnt lender c30 </th>  
                               <th class="whitespace">Insurance flag </th>
                               <th class="whitespace">Investor flag </th>
                               <th class="whitespace">Max approved loan amount c60 </th>
                               <th class="whitespace">Max dpd acc1  </th>
                               <th class="whitespace">Postpaid flag </th>
                               <th class="whitespace">Pre Paid flag </th>
                            </tr><tr>
                            <td class="whitespace">' . (($finbox['credit_card_user_flag']) ? $finbox['credit_card_user_flag'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['debit_card_user_flag']) ? $finbox['debit_card_user_flag'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['external_cnt_lender_c30']) ? $finbox['external_cnt_lender_c30'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['insurance_flag']) ? $finbox['insurance_flag'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['investor_flag']) ? $finbox['investor_flag'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['max_approved_loan_amount_c60']) ? $finbox['max_approved_loan_amount_c60'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['max_dpd_acc1']) ? $finbox['max_dpd_acc1'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['postpaid_flag']) ? $finbox['postpaid_flag'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['prepaid_flag']) ? $finbox['prepaid_flag'] : '-') . '</td>
                                </tr></tbody></table>

                            <table class="table table-bordered table-striped"><tbody><tr>                
                               <th class="whitespace">Salaried flag </th>
                               <th class="whitespace">Salary m123  </th>      
                               <th class="whitespace">SMS period  </th>  
                               <th class="whitespace">SMS vintage </th>
                               <th class="whitespace">Total avg bal 30  </th>
                               <th class="whitespace">Transactional sms count </th>
                               <th class="whitespace">CCL credit limit  </th>
                               <th class="whitespace">CCL bank </th>
                            </tr><tr>
                            <td class="whitespace">' . (($finbox['salaried_flag']) ? $finbox['salaried_flag'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['salary_m123']) ? $finbox['salary_m123'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['sms_period']) ? $finbox['sms_period'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['sms_vintage']) ? $finbox['sms_vintage'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['total_avg_bal_30']) ? $finbox['total_avg_bal_30'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['max_approved_loan_amount_c60']) ? $finbox['max_approved_loan_amount_c60'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['transactional_sms_count']) ? $finbox['transactional_sms_count'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['cc1_credit_limit']) ? $finbox['cc1_credit_limit'] : '-') . '</td>
                            <td class="whitespace">' . (($finbox['cc1_bank']) ? $finbox['cc1_bank'] : '-') . '</td>
                                </tr></tbody></table>

                                <table class="table table-bordered table-striped"><tbody><tr>                
                                <th class="whitespace">Cnt bounce senders </th>
                                <th class="whitespace">Cnt ewallet used </th>      
                                <th class="whitespace">Emi loan acc1 </th>  
                                <th class="whitespace">Cnt sms organization nbfc digital lender  </th>
                                <th class="whitespace">Acc0 acc number </th>
                                <th class="whitespace">Acc0 sms count </th>
                                <th class="whitespace">Acc0 avg bal m1 </th>
                                <th class="whitespace">Acc0 avg bal m2 </th>
                                <th class="whitespace">Acc0 avg bal m3 </th>
                             </tr><tr>
                             <td class="whitespace">' . (($finbox['cnt_bounce_senders']) ? $finbox['cnt_bounce_senders'] : '-') . '</td>
                             <td class="whitespace">' . (($finbox['cnt_ewallets_used']) ? $finbox['cnt_ewallets_used'] : '-') . '</td>
                             <td class="whitespace">' . (($finbox['emi_loan_acc1']) ? $finbox['emi_loan_acc1'] : '-') . '</td>
                             <td class="whitespace">' . (($finbox['cnt_sms_organization_nbfc_digital_lender']) ? $finbox['cnt_sms_organization_nbfc_digital_lender'] : '-') . '</td>
                             <td class="whitespace">' . (($finbox['acc0_acc_number']) ? $finbox['acc0_acc_number'] : '-') . '</td>
                             <td class="whitespace">' . (($finbox['acc0_sms_count']) ? $finbox['acc0_sms_count'] : '-') . '</td>
                             <td class="whitespace">' . (($finbox['acc0_avg_bal_m1']) ? $finbox['acc0_avg_bal_m1'] : '-') . '</td>
                             <td class="whitespace">' . (($finbox['acc0_avg_bal_m2']) ? $finbox['acc0_avg_bal_m2'] : '-') . '</td>
                             <td class="whitespace">' . (($finbox['acc0_avg_bal_m3']) ? $finbox['acc0_avg_bal_m3'] : '-') . '</td>
                                 </tr></tbody></table>

                                 <table class="table table-bordered table-striped"><tbody><tr>                
                                 <th class="whitespace">Cnt loan approved c30 </th>
                                 <th class="whitespace">Cnt loan rejected c30 </th>      
                                 <th class="whitespace">Amt credit txn m1 </th>  
                                 <th class="whitespace">Amt credit txn m2 </th>
                                 <th class="whitespace">Amt credit txn m3    </th>
                                 <th class="whitespace">Cnt credit txn m1 </th>
                                 <th class="whitespace">Cnt credit txn m2 </th>
                                 <th class="whitespace">Cnt credit txn m3  </th>
                             
                              </tr><tr>
                              <td class="whitespace">' . (($finbox['cnt_loan_approved_c30']) ? $finbox['cnt_loan_approved_c30'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cnt_loan_rejected_c30']) ? $finbox['cnt_loan_rejected_c30'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['amt_credit_txn_m1']) ? $finbox['amt_credit_txn_m1'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['amt_credit_txn_m2']) ? $finbox['amt_credit_txn_m2'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['amt_credit_txn_m3']) ? $finbox['amt_credit_txn_m3'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cnt_credit_txn_m1']) ? $finbox['cnt_credit_txn_m1'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cnt_credit_txn_m2']) ? $finbox['cnt_credit_txn_m2'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cnt_credit_txn_m3']) ? $finbox['cnt_credit_txn_m3'] : '-') . '</td>
                            
                                  </tr></tbody></table>

                            <table class="table table-bordered table-striped"><tbody><tr>                
                                 <th class="whitespace">Cnt debit txn m1 </th>
                                 <th class="whitespace">Cnt debit txn m2 </th>       
                                 <th class="whitespace">Cnt debit txn m3 </th>  
                                 <th class="whitespace">Amt debit txn m1 </th>
                                 <th class="whitespace">Amt debit txn m2  </th>
                                 <th class="whitespace">Amt debit txn m3  </th>
                                 
                              </tr><tr>
                              <td class="whitespace">' . (($finbox['cnt_debit_txn_m1']) ? $finbox['cnt_debit_txn_m1'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cnt_debit_txn_m2']) ? $finbox['cnt_debit_txn_m2'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['cnt_debit_txn_m3']) ? $finbox['cnt_debit_txn_m3'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['amt_debit_txn_m1']) ? $finbox['amt_debit_txn_m1'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['amt_debit_txn_m2']) ? $finbox['amt_debit_txn_m2'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['amt_debit_txn_m3']) ? $finbox['amt_debit_txn_m3'] : '-') . '</td>
                                  </tr></tbody></table>

                                  <table class="table table-bordered table-striped"><tbody><tr>                
                                  <th class="whitespace">Cnt existing bnpl acccounts </th>
                                  <th class="whitespace">Paytm postpaied flag </th>      
                                  <th class="whitespace">Amazon pay later flag </th>  
                                  <th class="whitespace">Bnpl vintage </th>
                                  <th class="whitespace">Bnpl recency  </th>
                                  <th class="whitespace">Cnt trn bnpl m1 </th>
                                  <th class="whitespace">Cnt trn bnpl m2 </th>
                                  <th class="whitespace">Cnt trn bnpl m3 </th>
                               </tr><tr>
                               <td class="whitespace">' . (($finbox['cnt_existing_bnpl_acccounts']) ? $finbox['cnt_existing_bnpl_acccounts'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['paytm_postpaied_flag']) ? $finbox['paytm_postpaied_flag'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['amazon_pay_later_flag']) ? $finbox['amazon_pay_later_flag'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['bnpl_vintage']) ? $finbox['bnpl_vintage'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['bnpl_recency']) ? $finbox['bnpl_recency'] : '-') . '</td>
                               <td class="whitespace">' . (($finbox['cnt_trn_bnpl_m1']) ? $finbox['cnt_trn_bnpl_m1'] : '-') . '</td> 
                               <td class="whitespace">' . (($finbox['cnt_trn_bnpl_m2']) ? $finbox['cnt_trn_bnpl_m2'] : '-') . '</td> 
                               <td class="whitespace">' . (($finbox['cnt_trn_bnpl_m3']) ? $finbox['cnt_trn_bnpl_m3'] : '-') . '</td> 
                                   </tr></tbody></table>

                            <table class="table table-bordered table-stripe"> </th>
                                 <th class="whitespace">Amt trn bnpl m1 </th>      
                                 <th class="whitespace">Amt trn bnpl m2 </th>  
                                 <th class="whitespace">Amt trn bnpl m3 </th>
                                 <th class="whitespace">Amt delinquency bnpl c30 </th>
                                 <th class="whitespace">Amt delinquency bnpl c60  </th>
                                 <th class="whitespace">Amt delinquency bnpl c30 </th>
                                 <th class="whitespace">Cnt delinquency bnpl c30  </th>
                                 <th class="whitespace">Cnt delinquency bnpl c60  </th>
                              </tr>
                            <tr>
                              <td class="whitespace">' . (($finbox['amt_trn_bnpl_m1']) ? $finbox['amt_trn_bnpl_m1'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['amt_trn_bnpl_m2']) ? $finbox['amt_trn_bnpl_m2'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['amt_trn_bnpl_m3']) ? $finbox['amt_trn_bnpl_m3'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['amt_delinquency_bnpl_c30']) ? $finbox['amt_delinquency_bnpl_c30'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['amt_delinquency_bnpl_c60']) ? $finbox['amt_delinquency_bnpl_c60'] : '-') . '</td>
                              <td class="whitespace">' . (($finbox['amt_delinquency_bnpl_c30']) ? $finbox['amt_delinquency_bnpl_c30'] : '-') . '</td> 
                              <td class="whitespace">' . (($finbox['cnt_delinquency_bnpl_c30']) ? $finbox['cnt_delinquency_bnpl_c30'] : '-') . '</td> 
                              <td class="whitespace">' . (($finbox['cnt_delinquency_bnpl_c60']) ? $finbox['cnt_delinquency_bnpl_c60'] : '-') . '</td> 
                            </tr>
                        </tbody>
                    </table>
                </div>';
print $return_data;
}else{
    echo '<span style="color:#e52255">Data is not found, Kindly fill correct details</span>';
}

?>