<?php


    // echo $fnbanking_detail[9]->finbox_bc_method_id;
     // $finbox = $fnbanking_details[2]->finbox_bc_response;
     // $fnbanking_detail = json_decode($finbox, TRUE);
     // echo '<pre>';
     // print_r($fnbanking_detail);

     if(!empty($fnbanking_details[0]->finbox_bc_response)){
     $finbox = $fnbanking_details[0]->finbox_bc_response;
     $fnbanking_detail = json_decode($finbox, TRUE);
      
     echo '<br><p>Upload API</p>';
     $return_data = '<div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Detail
          <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                              <table class="table table-bordered table-striped">
                              <tbody>
                                   <tr>                
                                        <th class="whitespace">Bank Name</th>                            
                                        <th class="whitespace">Statement Id</th>      
                                        <th class="whitespace">Entity Id</th> 
                                        <th class="whitespace">Account Number</th>    
                                   </tr>
                                   <tr>
                                        <td class="whitespace">' . (($fnbanking_detail['bank']) ? $fnbanking_detail['bank'] : '-') . '</td>
                                        <td class="whitespac">' . (($fnbanking_detail['statement_id']) ? $fnbanking_detail['statement_id'] : '-') . '</td>
                                        <td class="whitespace">' . (($fnbanking_detail['entity_id']) ? $fnbanking_detail['entity_id'] : '-') . '</td>
                                        <td class="whitespace">' . (($fnbanking_detail['identity']['account_number']) ? $fnbanking_detail['identity']['account_number'] : '-') . '</td>
                              </tr>
                              </tbody>
                              </table>
                              </div>
                                   <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Identity<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                      
                                        <th class="whitespace">Name</th> 
                                        <th class="whitespace">Address</th>  
                                        <th class="whitespace">IFSC</th> 
                                        <th class="whitespace">MICR</th> 
                                        <th class="whitespace">Account Category</th>  
                                        <th class="whitespace">Credit Limit</th> 
                                        <th class="whitespace">Account Id</th> 
                                   </tr><tr>
                                       
                                        <td class="whitespac">' . (($fnbanking_detail['identity']['name']) ? $fnbanking_detail['identity']['name'] : '-') . '</td>
                                        <td class="whitespace">' . (($fnbanking_detail['identity']['address']) ? $fnbanking_detail['identity']['address'] : '-') . '</td>
                                        <td class="whitespace">' . (($fnbanking_detail['identity']['ifsc']) ? $fnbanking_detail['identity']['ifsc'] : '-') . '</td>
                                        <td class="whitespac">' . (($fnbanking_detail['identity']['micr']) ? $fnbanking_detail['identity']['micr'] : '-') . '</td>
                                        <td class="whitespace">' . (($fnbanking_detail['identity']['account_category']) ? $fnbanking_detail['identity']['account_category'] : '-') . '</td>
                                        <td class="whitespace">' . (($fnbanking_detail['identity']['credit_limit']) ? $fnbanking_detail['identity']['credit_limit'] : '-') . '</td>
                                        <td class="whitespac">' . (($fnbanking_detail['identity']['account_id']) ? $fnbanking_detail['identity']['account_id'] : '-') . '</td>
                                   </tr></tbody></table></div>
                                   <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Account Reange <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                        <th class="whitespace">From Date</th> 
                                        <th class="whitespace">To Date</th>  
                                   </tr><tr>
                                        <td class="whitespace">' . (($fnbanking_detail['date_range']['from_date']) ? $fnbanking_detail['date_range']['from_date'] : '-') . '</td>
                                        <td class="whitespace">' . (($fnbanking_detail['date_range']['to_date']) ? $fnbanking_detail['date_range']['to_date'] : '-') . '</td>
                                   </tr></tbody></table></div>
                                   
                                   <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Valid?  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                        <th class="whitespace">Is fraud.</th> 
                                        <th class="whitespace">Fraud Type</th> 
                                        <th class="whitespace">Page_count</th> 
                                      
                                   </tr><tr>
                                        <td class="whitespace">' . (($fnbanking_detail['is_fraud']) ? $fnbanking_detail['fraud']['fraudulent_statements'][0]: '-') . '</td>
                                        <td class="whitespace">' . (($fnbanking_detail['fraud_type']) ? $fnbanking_detail['fraud']['fraud_type'][0]['statement_id']: '-') . '</td>
                                        <td class="whitespac">' . (($fnbanking_detail['page_count'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] : '-') . '</td>
                                        
                                   
                                   </tr></tbody></table></div>
          
                                                  </div>';

                                                  print $return_data;
     }
          if(!empty($fnbanking_details[1]->finbox_bc_response)){
          $finbox = $fnbanking_details[1]->finbox_bc_response;
          $fnbanking_detail = json_decode($finbox, TRUE);
          // echo '<pre>';
          // print_r($fnbanking_detail);
          echo '<p>List Account</p>';
          $return_data = '<div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Detail
               <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                                   <table class="table table-bordered table-striped">
                                   <tbody>
                                        <tr>                
                                             <th class="whitespace">Bank Name</th>                            
                                             <th class="whitespace">Statement Id</th>      
                                             <th class="whitespace">Entity Id</th>    
                                             <th class="whitespace">Account Number</th> 
                                        </tr>
                                        <tr>
                                             <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['bank']) ? $fnbanking_detail['accounts'][0]['bank'] : '-') . '</td>
                                             <td class="whitespac">' .  (($fnbanking_detail['progress'][0]['statement_id']) ? $fnbanking_detail['progress'][0]['statement_id'] : '-') . '</td>
                                             <td class="whitespace">' . (($fnbanking_detail['entity_id']) ? $fnbanking_detail['entity_id'] : '-') . '</td>
                                             <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['account_number']) ? $fnbanking_detail['accounts'][0]['account_number'] : '-') . '</td> 
                                   </tr>
                                   </tbody>
                                   </table>
                                   </div>
                                        <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Identity<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                            
                                             <th class="whitespace">Name</th> 
                                             <th class="whitespace">Address</th>  
                                             <th class="whitespace">IFSC</th> 
                                             <th class="whitespace">MICR</th> 
                                             <th class="whitespace">Account Category</th>  
                                             <th class="whitespace">Credit Limit</th> 
                                             <th class="whitespace">Account Id</th> 
                                        </tr><tr>
                                             
                                             <td class="whitespac">' . (($fnbanking_detail['identity'][0]['name']) ? $fnbanking_detail['identity'][0]['name'] : '-') . '</td>
                                             <td class="whitespace">' . (($fnbanking_detail['identity'][0]['address']) ? $fnbanking_detail['identity'][0]['address'] : '-') . '</td>
                                             <td class="whitespace">' . (($fnbanking_detail['identity'][0]['ifsc']) ? $fnbanking_detail['identity'][0]['ifsc'] : '-') . '</td>
                                             <td class="whitespac">' . (($fnbanking_detail['identity'][0]['micr']) ? $fnbanking_detail['identity'][0]['micr'] : '-') . '</td>
                                             <td class="whitespace">' . (($fnbanking_detail['identity'][0]['account_category']) ? $fnbanking_detail['identity'][0]['account_category'] : '-') . '</td>
                                             <td class="whitespace">' . (($fnbanking_detail['identity'][0]['credit_limit']) ? $fnbanking_detail['identity'][0]['credit_limit'] : '-') . '</td>
                                             <td class="whitespac">' . (($fnbanking_detail['identity'][0]['account_id']) ? $fnbanking_detail['identity'][0]['account_id'] : '-') . '</td>
                                        </tr></tbody></table></div>
                                        <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Account Reange <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                             <th class="whitespace">From Date</th> 
                                             <th class="whitespace">To Date</th>  
                                        </tr><tr>
                                             <td class="whitespace">' . (($fnbanking_detail['date_range']['from_date']) ? $fnbanking_detail['date_range']['from_date'] : '-') . '</td>
                                             <td class="whitespace">' . (($fnbanking_detail['date_range']['to_date']) ? $fnbanking_detail['date_range']['to_date'] : '-') . '</td>
                                        </tr></tbody></table></div>
                                        
                                        <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Valid?  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                             <th class="whitespace">Fraudulent Stmt.</th> 
                                             <th class="whitespace"> Stmt. Id</th> 
                                             <th class="whitespace">Fraud Type</th> 
                                             <th class="whitespace">Account Id</th> 
                                             <th class="whitespace">Trans. Hash</th> 
                                             <th class="whitespace">Fraud Cate./th> 
                                        </tr><tr>
                                             <td class="whitespace">' . (($fnbanking_detail['fraud']['fraudulent_statements'][0]) ? $fnbanking_detail['fraud']['fraudulent_statements'][0]: '-') . '</td>
                                             <td class="whitespace">' . (($fnbanking_detail['fraud']['fraud_type'][0]['statement_id']) ? $fnbanking_detail['fraud']['fraud_type'][0]['statement_id']: '-') . '</td>
                                             <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] : '-') . '</td>
                                             <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['account_id'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['account_id'] : '-') . '</td>
                                             <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] : '-') . '</td>
                                             <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] : '-') . '</td>
                                        
                                        </tr></tbody></table></div>
                                        
                                        
               
                                                       </div>';
                          print $return_data;
          }
     
          if(!empty($fnbanking_details[2]->finbox_bc_response)){
               $finbox = $fnbanking_details[2]->finbox_bc_response;
               $fnbanking_detail = json_decode($finbox, TRUE);
               // echo '<pre>';
               // print_r($fnbanking_detail);
               echo '<p>Identity</p>';
               $return_data = '<div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Detail
                    <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                                        <table class="table table-bordered table-striped">
                                        <tbody>
                                             <tr>                
                                                  <th class="whitespace">Bank Name</th>                            
                                                  <th class="whitespace">Statement Id</th>      
                                                  <th class="whitespace">Entity Id</th>    
                                                  <th class="whitespace">Account Number</th> 
                                             </tr>
                                             <tr>
                                                  <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['bank']) ? $fnbanking_detail['accounts'][0]['bank'] : '-') . '</td>
                                                  <td class="whitespac">' .  (($fnbanking_detail['progress'][0]['statement_id']) ? $fnbanking_detail['progress'][0]['statement_id'] : '-') . '</td>
                                                  <td class="whitespace">' . (($fnbanking_detail['entity_id']) ? $fnbanking_detail['entity_id'] : '-') . '</td>
                                                  <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['account_number']) ? $fnbanking_detail['accounts'][0]['account_number'] : '-') . '</td> 
                                        </tr>
                                        </tbody>
                                        </table>
                                        </div>
                                             <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Identity<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                 
                                                  <th class="whitespace">Name</th> 
                                                  <th class="whitespace">Address</th>  
                                                  <th class="whitespace">IFSC</th> 
                                                  <th class="whitespace">MICR</th> 
                                                  <th class="whitespace">Account Category</th>  
                                                  <th class="whitespace">Credit Limit</th> 
                                                  <th class="whitespace">Account Id</th> 
                                             </tr><tr>
                                                  
                                                  <td class="whitespac">' . (($fnbanking_detail['identity'][0]['name']) ? $fnbanking_detail['identity'][0]['name'] : '-') . '</td>
                                                  <td class="whitespace">' . (($fnbanking_detail['identity'][0]['address']) ? $fnbanking_detail['identity'][0]['address'] : '-') . '</td>
                                                  <td class="whitespace">' . (($fnbanking_detail['identity'][0]['ifsc']) ? $fnbanking_detail['identity'][0]['ifsc'] : '-') . '</td>
                                                  <td class="whitespac">' . (($fnbanking_detail['identity'][0]['micr']) ? $fnbanking_detail['identity'][0]['micr'] : '-') . '</td>
                                                  <td class="whitespace">' . (($fnbanking_detail['identity'][0]['account_category']) ? $fnbanking_detail['identity'][0]['account_category'] : '-') . '</td>
                                                  <td class="whitespace">' . (($fnbanking_detail['identity'][0]['credit_limit']) ? $fnbanking_detail['identity'][0]['credit_limit'] : '-') . '</td>
                                                  <td class="whitespac">' . (($fnbanking_detail['identity'][0]['account_id']) ? $fnbanking_detail['identity'][0]['account_id'] : '-') . '</td>
                                             </tr></tbody></table></div>
                                             <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Account Reange <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                  <th class="whitespace">From Date</th> 
                                                  <th class="whitespace">To Date</th>  
                                             </tr><tr>
                                                  <td class="whitespace">' . (($fnbanking_detail['date_range']['from_date']) ? $fnbanking_detail['date_range']['from_date'] : '-') . '</td>
                                                  <td class="whitespace">' . (($fnbanking_detail['date_range']['to_date']) ? $fnbanking_detail['date_range']['to_date'] : '-') . '</td>
                                             </tr></tbody></table></div>
                                             
                                             <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Valid?  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                  <th class="whitespace">Fraudulent Stmt.</th> 
                                                  <th class="whitespace"> Stmt. Id</th> 
                                                  <th class="whitespace">Fraud Type</th> 
                                                  <th class="whitespace">Account Id</th> 
                                                  <th class="whitespace">Trans. Hash</th> 
                                                  <th class="whitespace">Fraud Cate./th> 
                                             </tr><tr>
                                                  <td class="whitespace">' . (($fnbanking_detail['fraud']['fraudulent_statements'][0]) ? $fnbanking_detail['fraud']['fraudulent_statements'][0]: '-') . '</td>
                                                  <td class="whitespace">' . (($fnbanking_detail['fraud']['fraud_type'][0]['statement_id']) ? $fnbanking_detail['fraud']['fraud_type'][0]['statement_id']: '-') . '</td>
                                                  <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] : '-') . '</td>
                                                  <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['account_id'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['account_id'] : '-') . '</td>
                                                  <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] : '-') . '</td>
                                                  <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] : '-') . '</td>
                                             
                                             </tr></tbody></table></div>
                                             
                                             
                    
                                                            </div>';
                               print $return_data;
               }

               if(!empty($fnbanking_details[3]->finbox_bc_response)){
                    $finbox = $fnbanking_details[3]->finbox_bc_response;
                    $fnbanking_detail = json_decode($finbox, TRUE);
                    // echo '<pre>';
                    // print_r($fnbanking_detail);
                    echo '<p>Transactions</p>';
                    $return_data = '<div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Detail
                         <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                                             <table class="table table-bordered table-striped">
                                             <tbody>
                                                  <tr>                
                                                       <th class="whitespace">Bank Name</th>                            
                                                       <th class="whitespace">Statement Id</th>      
                                                       <th class="whitespace">Entity Id</th>    
                                                       <th class="whitespace">Account Number</th> 
                                                  </tr>
                                                  <tr>
                                                       <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['bank']) ? $fnbanking_detail['accounts'][0]['bank'] : '-') . '</td>
                                                       <td class="whitespac">' .  (($fnbanking_detail['progress'][0]['statement_id']) ? $fnbanking_detail['progress'][0]['statement_id'] : '-') . '</td>
                                                       <td class="whitespace">' . (($fnbanking_detail['entity_id']) ? $fnbanking_detail['entity_id'] : '-') . '</td>
                                                       <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['account_number']) ? $fnbanking_detail['accounts'][0]['account_number'] : '-') . '</td> 
                                             </tr>
                                             </tbody>
                                             </table>
                                             </div>
                                                  <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Identity<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                      
                                                       <th class="whitespace">Name</th> 
                                                       <th class="whitespace">Address</th>  
                                                       <th class="whitespace">IFSC</th> 
                                                       <th class="whitespace">MICR</th> 
                                                       <th class="whitespace">Account Category</th>  
                                                       <th class="whitespace">Credit Limit</th> 
                                                       <th class="whitespace">Account Id</th> 
                                                  </tr><tr>
                                                       
                                                       <td class="whitespac">' . (($fnbanking_detail['identity'][0]['name']) ? $fnbanking_detail['identity'][0]['name'] : '-') . '</td>
                                                       <td class="whitespace">' . (($fnbanking_detail['identity'][0]['address']) ? $fnbanking_detail['identity'][0]['address'] : '-') . '</td>
                                                       <td class="whitespace">' . (($fnbanking_detail['identity'][0]['ifsc']) ? $fnbanking_detail['identity'][0]['ifsc'] : '-') . '</td>
                                                       <td class="whitespac">' . (($fnbanking_detail['identity'][0]['micr']) ? $fnbanking_detail['identity'][0]['micr'] : '-') . '</td>
                                                       <td class="whitespace">' . (($fnbanking_detail['identity'][0]['account_category']) ? $fnbanking_detail['identity'][0]['account_category'] : '-') . '</td>
                                                       <td class="whitespace">' . (($fnbanking_detail['identity'][0]['credit_limit']) ? $fnbanking_detail['identity'][0]['credit_limit'] : '-') . '</td>
                                                       <td class="whitespac">' . (($fnbanking_detail['identity'][0]['account_id']) ? $fnbanking_detail['identity'][0]['account_id'] : '-') . '</td>
                                                  </tr></tbody></table></div>
                                                  <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Account Reange <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                       <th class="whitespace">From Date</th> 
                                                       <th class="whitespace">To Date</th>  
                                                  </tr><tr>
                                                       <td class="whitespace">' . (($fnbanking_detail['date_range']['from_date']) ? $fnbanking_detail['date_range']['from_date'] : '-') . '</td>
                                                       <td class="whitespace">' . (($fnbanking_detail['date_range']['to_date']) ? $fnbanking_detail['date_range']['to_date'] : '-') . '</td>
                                                  </tr></tbody></table></div>
                                                  
                                                  <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Valid?  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                       <th class="whitespace">Fraudulent Stmt.</th> 
                                                       <th class="whitespace"> Stmt. Id</th> 
                                                       <th class="whitespace">Fraud Type</th> 
                                                       <th class="whitespace">Account Id</th> 
                                                       <th class="whitespace">Trans. Hash</th> 
                                                       <th class="whitespace">Fraud Cate./th> 
                                                  </tr><tr>
                                                       <td class="whitespace">' . (($fnbanking_detail['fraud']['fraudulent_statements'][0]) ? $fnbanking_detail['fraud']['fraudulent_statements'][0]: '-') . '</td>
                                                       <td class="whitespace">' . (($fnbanking_detail['fraud']['fraud_type'][0]['statement_id']) ? $fnbanking_detail['fraud']['fraud_type'][0]['statement_id']: '-') . '</td>
                                                       <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] : '-') . '</td>
                                                       <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['account_id'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['account_id'] : '-') . '</td>
                                                       <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] : '-') . '</td>
                                                       <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] : '-') . '</td>
                                                  
                                                  </tr></tbody></table></div>
                                                  
                                                  
                         
                                                                 </div>';
                                    print $return_data;
                    }
                    

                    if(!empty($fnbanking_details[4]->finbox_bc_response)){
                         $finbox = $fnbanking_details[4]->finbox_bc_response;
                         $fnbanking_detail = json_decode($finbox, TRUE);
                         // echo '<pre>';
                         // print_r($fnbanking_detail);
                         echo '<p>Salary</p>';
                         $return_data = '<div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Detail
                              <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                                                  <table class="table table-bordered table-striped">
                                                  <tbody>
                                                       <tr>                
                                                            <th class="whitespace">Bank Name</th>                            
                                                            <th class="whitespace">Statement Id</th>      
                                                            <th class="whitespace">Entity Id</th>    
                                                            <th class="whitespace">Account Number</th> 
                                                       </tr>
                                                       <tr>
                                                            <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['bank']) ? $fnbanking_detail['accounts'][0]['bank'] : '-') . '</td>
                                                            <td class="whitespac">' .  (($fnbanking_detail['progress'][0]['statement_id']) ? $fnbanking_detail['progress'][0]['statement_id'] : '-') . '</td>
                                                            <td class="whitespace">' . (($fnbanking_detail['entity_id']) ? $fnbanking_detail['entity_id'] : '-') . '</td>
                                                            <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['account_number']) ? $fnbanking_detail['accounts'][0]['account_number'] : '-') . '</td> 
                                                  </tr>
                                                  </tbody>
                                                  </table>
                                                  </div>
                                                       <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Identity<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                           
                                                            <th class="whitespace">Name</th> 
                                                            <th class="whitespace">Address</th>  
                                                            <th class="whitespace">IFSC</th> 
                                                            <th class="whitespace">MICR</th> 
                                                            <th class="whitespace">Account Category</th>  
                                                            <th class="whitespace">Credit Limit</th> 
                                                            <th class="whitespace">Account Id</th> 
                                                       </tr><tr>
                                                            
                                                            <td class="whitespac">' . (($fnbanking_detail['identity'][0]['name']) ? $fnbanking_detail['identity'][0]['name'] : '-') . '</td>
                                                            <td class="whitespace">' . (($fnbanking_detail['identity'][0]['address']) ? $fnbanking_detail['identity'][0]['address'] : '-') . '</td>
                                                            <td class="whitespace">' . (($fnbanking_detail['identity'][0]['ifsc']) ? $fnbanking_detail['identity'][0]['ifsc'] : '-') . '</td>
                                                            <td class="whitespac">' . (($fnbanking_detail['identity'][0]['micr']) ? $fnbanking_detail['identity'][0]['micr'] : '-') . '</td>
                                                            <td class="whitespace">' . (($fnbanking_detail['identity'][0]['account_category']) ? $fnbanking_detail['identity'][0]['account_category'] : '-') . '</td>
                                                            <td class="whitespace">' . (($fnbanking_detail['identity'][0]['credit_limit']) ? $fnbanking_detail['identity'][0]['credit_limit'] : '-') . '</td>
                                                            <td class="whitespac">' . (($fnbanking_detail['identity'][0]['account_id']) ? $fnbanking_detail['identity'][0]['account_id'] : '-') . '</td>
                                                       </tr></tbody></table></div>
                                                       <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Account Reange <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                            <th class="whitespace">From Date</th> 
                                                            <th class="whitespace">To Date</th>  
                                                       </tr><tr>
                                                            <td class="whitespace">' . (($fnbanking_detail['date_range']['from_date']) ? $fnbanking_detail['date_range']['from_date'] : '-') . '</td>
                                                            <td class="whitespace">' . (($fnbanking_detail['date_range']['to_date']) ? $fnbanking_detail['date_range']['to_date'] : '-') . '</td>
                                                       </tr></tbody></table></div>
                                                       
                                                       <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Valid?  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                            <th class="whitespace">Fraudulent Stmt.</th> 
                                                            <th class="whitespace"> Stmt. Id</th> 
                                                            <th class="whitespace">Fraud Type</th> 
                                                            <th class="whitespace">Account Id</th> 
                                                            <th class="whitespace">Trans. Hash</th> 
                                                            <th class="whitespace">Fraud Cate./th> 
                                                       </tr><tr>
                                                            <td class="whitespace">' . (($fnbanking_detail['fraud']['fraudulent_statements'][0]) ? $fnbanking_detail['fraud']['fraudulent_statements'][0]: '-') . '</td>
                                                            <td class="whitespace">' . (($fnbanking_detail['fraud']['fraud_type'][0]['statement_id']) ? $fnbanking_detail['fraud']['fraud_type'][0]['statement_id']: '-') . '</td>
                                                            <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] : '-') . '</td>
                                                            <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['account_id'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['account_id'] : '-') . '</td>
                                                            <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] : '-') . '</td>
                                                            <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] : '-') . '</td>
                                                       
                                                       </tr></tbody></table></div>
                                                       
                                                       
                              
                                                                      </div>';
                                         print $return_data;
                         }

                         if(!empty($fnbanking_details[5]->finbox_bc_response)){
                              $finbox = $fnbanking_details[5]->finbox_bc_response;
                              $fnbanking_detail = json_decode($finbox, TRUE);
                              // echo '<pre>';
                              // print_r($fnbanking_detail);
                              echo '<p>Recurring Transctions</p>';
                              $return_data = '<div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Detail
                                   <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                                                       <table class="table table-bordered table-striped">
                                                       <tbody>
                                                            <tr>                
                                                                 <th class="whitespace">Bank Name</th>                            
                                                                 <th class="whitespace">Statement Id</th>      
                                                                 <th class="whitespace">Entity Id</th>    
                                                                 <th class="whitespace">Account Number</th> 
                                                            </tr>
                                                            <tr>
                                                                 <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['bank']) ? $fnbanking_detail['accounts'][0]['bank'] : '-') . '</td>
                                                                 <td class="whitespac">' .  (($fnbanking_detail['progress'][0]['statement_id']) ? $fnbanking_detail['progress'][0]['statement_id'] : '-') . '</td>
                                                                 <td class="whitespace">' . (($fnbanking_detail['entity_id']) ? $fnbanking_detail['entity_id'] : '-') . '</td>
                                                                 <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['account_number']) ? $fnbanking_detail['accounts'][0]['account_number'] : '-') . '</td> 
                                                       </tr>
                                                       </tbody>
                                                       </table>
                                                       </div>
                                                            <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Identity<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                                
                                                                 <th class="whitespace">Name</th> 
                                                                 <th class="whitespace">Address</th>  
                                                                 <th class="whitespace">IFSC</th> 
                                                                 <th class="whitespace">MICR</th> 
                                                                 <th class="whitespace">Account Category</th>  
                                                                 <th class="whitespace">Credit Limit</th> 
                                                                 <th class="whitespace">Account Id</th> 
                                                            </tr><tr>
                                                                 
                                                                 <td class="whitespac">' . (($fnbanking_detail['identity'][0]['name']) ? $fnbanking_detail['identity'][0]['name'] : '-') . '</td>
                                                                 <td class="whitespace">' . (($fnbanking_detail['identity'][0]['address']) ? $fnbanking_detail['identity'][0]['address'] : '-') . '</td>
                                                                 <td class="whitespace">' . (($fnbanking_detail['identity'][0]['ifsc']) ? $fnbanking_detail['identity'][0]['ifsc'] : '-') . '</td>
                                                                 <td class="whitespac">' . (($fnbanking_detail['identity'][0]['micr']) ? $fnbanking_detail['identity'][0]['micr'] : '-') . '</td>
                                                                 <td class="whitespace">' . (($fnbanking_detail['identity'][0]['account_category']) ? $fnbanking_detail['identity'][0]['account_category'] : '-') . '</td>
                                                                 <td class="whitespace">' . (($fnbanking_detail['identity'][0]['credit_limit']) ? $fnbanking_detail['identity'][0]['credit_limit'] : '-') . '</td>
                                                                 <td class="whitespac">' . (($fnbanking_detail['identity'][0]['account_id']) ? $fnbanking_detail['identity'][0]['account_id'] : '-') . '</td>
                                                            </tr></tbody></table></div>
                                                            <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Account Reange <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                                 <th class="whitespace">From Date</th> 
                                                                 <th class="whitespace">To Date</th>  
                                                            </tr><tr>
                                                                 <td class="whitespace">' . (($fnbanking_detail['date_range']['from_date']) ? $fnbanking_detail['date_range']['from_date'] : '-') . '</td>
                                                                 <td class="whitespace">' . (($fnbanking_detail['date_range']['to_date']) ? $fnbanking_detail['date_range']['to_date'] : '-') . '</td>
                                                            </tr></tbody></table></div>
                                                            
                                                            <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Valid?  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                                 <th class="whitespace">Fraudulent Stmt.</th> 
                                                                 <th class="whitespace"> Stmt. Id</th> 
                                                                 <th class="whitespace">Fraud Type</th> 
                                                                 <th class="whitespace">Account Id</th> 
                                                                 <th class="whitespace">Trans. Hash</th> 
                                                                 <th class="whitespace">Fraud Cate./th> 
                                                            </tr><tr>
                                                                 <td class="whitespace">' . (($fnbanking_detail['fraud']['fraudulent_statements'][0]) ? $fnbanking_detail['fraud']['fraudulent_statements'][0]: '-') . '</td>
                                                                 <td class="whitespace">' . (($fnbanking_detail['fraud']['fraud_type'][0]['statement_id']) ? $fnbanking_detail['fraud']['fraud_type'][0]['statement_id']: '-') . '</td>
                                                                 <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] : '-') . '</td>
                                                                 <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['account_id'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['account_id'] : '-') . '</td>
                                                                 <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] : '-') . '</td>
                                                                 <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] : '-') . '</td>
                                                            
                                                            </tr></tbody></table></div>
                                                            
                                                            
                                   
                                                                           </div>';
                                              print $return_data;
                              }

                              if(!empty($fnbanking_details[6]->finbox_bc_response)){
                                   $finbox = $fnbanking_details[6]->finbox_bc_response;
                                   $fnbanking_detail = json_decode($finbox, TRUE);
                                   // echo '<pre>';
                                   // print_r($fnbanking_detail);
                                   echo '<p>Lending Transaction</p>';
                                   $return_data = '<div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Detail
                                        <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                                                            <table class="table table-bordered table-striped">
                                                            <tbody>
                                                                 <tr>                
                                                                      <th class="whitespace">Bank Name</th>                            
                                                                      <th class="whitespace">Statement Id</th>      
                                                                      <th class="whitespace">Entity Id</th>    
                                                                      <th class="whitespace">Account Number</th> 
                                                                 </tr>
                                                                 <tr>
                                                                      <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['bank']) ? $fnbanking_detail['accounts'][0]['bank'] : '-') . '</td>
                                                                      <td class="whitespac">' .  (($fnbanking_detail['progress'][0]['statement_id']) ? $fnbanking_detail['progress'][0]['statement_id'] : '-') . '</td>
                                                                      <td class="whitespace">' . (($fnbanking_detail['entity_id']) ? $fnbanking_detail['entity_id'] : '-') . '</td>
                                                                      <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['account_number']) ? $fnbanking_detail['accounts'][0]['account_number'] : '-') . '</td> 
                                                            </tr>
                                                            </tbody>
                                                            </table>
                                                            </div>
                                                                 <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Identity<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                                     
                                                                      <th class="whitespace">Name</th> 
                                                                      <th class="whitespace">Address</th>  
                                                                      <th class="whitespace">IFSC</th> 
                                                                      <th class="whitespace">MICR</th> 
                                                                      <th class="whitespace">Account Category</th>  
                                                                      <th class="whitespace">Credit Limit</th> 
                                                                      <th class="whitespace">Account Id</th> 
                                                                 </tr><tr>
                                                                      
                                                                      <td class="whitespac">' . (($fnbanking_detail['identity'][0]['name']) ? $fnbanking_detail['identity'][0]['name'] : '-') . '</td>
                                                                      <td class="whitespace">' . (($fnbanking_detail['identity'][0]['address']) ? $fnbanking_detail['identity'][0]['address'] : '-') . '</td>
                                                                      <td class="whitespace">' . (($fnbanking_detail['identity'][0]['ifsc']) ? $fnbanking_detail['identity'][0]['ifsc'] : '-') . '</td>
                                                                      <td class="whitespac">' . (($fnbanking_detail['identity'][0]['micr']) ? $fnbanking_detail['identity'][0]['micr'] : '-') . '</td>
                                                                      <td class="whitespace">' . (($fnbanking_detail['identity'][0]['account_category']) ? $fnbanking_detail['identity'][0]['account_category'] : '-') . '</td>
                                                                      <td class="whitespace">' . (($fnbanking_detail['identity'][0]['credit_limit']) ? $fnbanking_detail['identity'][0]['credit_limit'] : '-') . '</td>
                                                                      <td class="whitespac">' . (($fnbanking_detail['identity'][0]['account_id']) ? $fnbanking_detail['identity'][0]['account_id'] : '-') . '</td>
                                                                 </tr></tbody></table></div>
                                                                 <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Account Reange <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                                      <th class="whitespace">From Date</th> 
                                                                      <th class="whitespace">To Date</th>  
                                                                 </tr><tr>
                                                                      <td class="whitespace">' . (($fnbanking_detail['date_range']['from_date']) ? $fnbanking_detail['date_range']['from_date'] : '-') . '</td>
                                                                      <td class="whitespace">' . (($fnbanking_detail['date_range']['to_date']) ? $fnbanking_detail['date_range']['to_date'] : '-') . '</td>
                                                                 </tr></tbody></table></div>
                                                                 
                                                                 <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Valid?  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                                      <th class="whitespace">Fraudulent Stmt.</th> 
                                                                      <th class="whitespace"> Stmt. Id</th> 
                                                                      <th class="whitespace">Fraud Type</th> 
                                                                      <th class="whitespace">Account Id</th> 
                                                                      <th class="whitespace">Trans. Hash</th> 
                                                                      <th class="whitespace">Fraud Cate./th> 
                                                                 </tr><tr>
                                                                      <td class="whitespace">' . (($fnbanking_detail['fraud']['fraudulent_statements'][0]) ? $fnbanking_detail['fraud']['fraudulent_statements'][0]: '-') . '</td>
                                                                      <td class="whitespace">' . (($fnbanking_detail['fraud']['fraud_type'][0]['statement_id']) ? $fnbanking_detail['fraud']['fraud_type'][0]['statement_id']: '-') . '</td>
                                                                      <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] : '-') . '</td>
                                                                      <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['account_id'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['account_id'] : '-') . '</td>
                                                                      <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] : '-') . '</td>
                                                                      <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] : '-') . '</td>
                                                                 
                                                                 </tr></tbody></table></div>
                                                                 
                                                                 
                                        
                                                                                </div>';
                                                   print $return_data;
                                   }
    
                                   if(!empty($fnbanking_details[7]->finbox_bc_response)){
                                        $finbox = $fnbanking_details[7]->finbox_bc_response;
                                        $fnbanking_detail = json_decode($finbox, TRUE);
                                        // echo '<pre>';
                                        // print_r($fnbanking_detail);
                                        echo '<p>Get Expense Category</p>';
                                        $return_data = '<div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Detail
                                             <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                                                                 <table class="table table-bordered table-striped">
                                                                 <tbody>
                                                                      <tr>                
                                                                           <th class="whitespace">Bank Name</th>                            
                                                                           <th class="whitespace">Statement Id</th>      
                                                                           <th class="whitespace">Entity Id</th>    
                                                                           <th class="whitespace">Account Number</th> 
                                                                      </tr>
                                                                      <tr>
                                                                           <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['bank']) ? $fnbanking_detail['accounts'][0]['bank'] : '-') . '</td>
                                                                           <td class="whitespac">' .  (($fnbanking_detail['progress'][0]['statement_id']) ? $fnbanking_detail['progress'][0]['statement_id'] : '-') . '</td>
                                                                           <td class="whitespace">' . (($fnbanking_detail['entity_id']) ? $fnbanking_detail['entity_id'] : '-') . '</td>
                                                                           <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['account_number']) ? $fnbanking_detail['accounts'][0]['account_number'] : '-') . '</td> 
                                                                 </tr>
                                                                 </tbody>
                                                                 </table>
                                                                 </div>
                                                                      <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Identity<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                                          
                                                                           <th class="whitespace">Name</th> 
                                                                           <th class="whitespace">Address</th>  
                                                                           <th class="whitespace">IFSC</th> 
                                                                           <th class="whitespace">MICR</th> 
                                                                           <th class="whitespace">Account Category</th>  
                                                                           <th class="whitespace">Credit Limit</th> 
                                                                           <th class="whitespace">Account Id</th> 
                                                                      </tr><tr>
                                                                           
                                                                           <td class="whitespac">' . (($fnbanking_detail['identity'][0]['name']) ? $fnbanking_detail['identity'][0]['name'] : '-') . '</td>
                                                                           <td class="whitespace">' . (($fnbanking_detail['identity'][0]['address']) ? $fnbanking_detail['identity'][0]['address'] : '-') . '</td>
                                                                           <td class="whitespace">' . (($fnbanking_detail['identity'][0]['ifsc']) ? $fnbanking_detail['identity'][0]['ifsc'] : '-') . '</td>
                                                                           <td class="whitespac">' . (($fnbanking_detail['identity'][0]['micr']) ? $fnbanking_detail['identity'][0]['micr'] : '-') . '</td>
                                                                           <td class="whitespace">' . (($fnbanking_detail['identity'][0]['account_category']) ? $fnbanking_detail['identity'][0]['account_category'] : '-') . '</td>
                                                                           <td class="whitespace">' . (($fnbanking_detail['identity'][0]['credit_limit']) ? $fnbanking_detail['identity'][0]['credit_limit'] : '-') . '</td>
                                                                           <td class="whitespac">' . (($fnbanking_detail['identity'][0]['account_id']) ? $fnbanking_detail['identity'][0]['account_id'] : '-') . '</td>
                                                                      </tr></tbody></table></div>
                                                                      <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Account Reange <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                                           <th class="whitespace">From Date</th> 
                                                                           <th class="whitespace">To Date</th>  
                                                                      </tr><tr>
                                                                           <td class="whitespace">' . (($fnbanking_detail['date_range']['from_date']) ? $fnbanking_detail['date_range']['from_date'] : '-') . '</td>
                                                                           <td class="whitespace">' . (($fnbanking_detail['date_range']['to_date']) ? $fnbanking_detail['date_range']['to_date'] : '-') . '</td>
                                                                      </tr></tbody></table></div>
                                                                      
                                                                      <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Valid?  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                                           <th class="whitespace">Fraudulent Stmt.</th> 
                                                                           <th class="whitespace"> Stmt. Id</th> 
                                                                           <th class="whitespace">Fraud Type</th> 
                                                                           <th class="whitespace">Account Id</th> 
                                                                           <th class="whitespace">Trans. Hash</th> 
                                                                           <th class="whitespace">Fraud Cate./th> 
                                                                      </tr><tr>
                                                                           <td class="whitespace">' . (($fnbanking_detail['fraud']['fraudulent_statements'][0]) ? $fnbanking_detail['fraud']['fraudulent_statements'][0]: '-') . '</td>
                                                                           <td class="whitespace">' . (($fnbanking_detail['fraud']['fraud_type'][0]['statement_id']) ? $fnbanking_detail['fraud']['fraud_type'][0]['statement_id']: '-') . '</td>
                                                                           <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] : '-') . '</td>
                                                                           <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['account_id'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['account_id'] : '-') . '</td>
                                                                           <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] : '-') . '</td>
                                                                           <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] : '-') . '</td>
                                                                      
                                                                      </tr></tbody></table></div>
                                                                      
                                                                      
                                             
                                                                                     </div>';
                                                        print $return_data;
                                        }

                                        if(!empty($fnbanking_details[8]->finbox_bc_response)){
                                             $finbox = $fnbanking_details[8]->finbox_bc_response;
                                             $fnbanking_detail = json_decode($finbox, TRUE);
                                             // echo '<pre>';
                                             // print_r($fnbanking_detail);
                                             echo '<p>Monthly Analysis</p>';
                                             $return_data = '<div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Detail
                                                  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                                                                      <table class="table table-bordered table-striped">
                                                                      <tbody>
                                                                           <tr>                
                                                                                <th class="whitespace">Bank Name</th>                            
                                                                                <th class="whitespace">Statement Id</th>      
                                                                                <th class="whitespace">Entity Id</th>    
                                                                                <th class="whitespace">Account Number</th> 
                                                                           </tr>
                                                                           <tr>
                                                                                <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['bank']) ? $fnbanking_detail['accounts'][0]['bank'] : '-') . '</td>
                                                                                <td class="whitespac">' .  (($fnbanking_detail['progress'][0]['statement_id']) ? $fnbanking_detail['progress'][0]['statement_id'] : '-') . '</td>
                                                                                <td class="whitespace">' . (($fnbanking_detail['entity_id']) ? $fnbanking_detail['entity_id'] : '-') . '</td>
                                                                                <td class="whitespace">' . (($fnbanking_detail['accounts'][0]['account_number']) ? $fnbanking_detail['accounts'][0]['account_number'] : '-') . '</td> 
                                                                      </tr>
                                                                      </tbody>
                                                                      </table>
                                                                      </div>
                                                                           <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Identity<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                                               
                                                                                <th class="whitespace">Name</th> 
                                                                                <th class="whitespace">Address</th>  
                                                                                <th class="whitespace">IFSC</th> 
                                                                                <th class="whitespace">MICR</th> 
                                                                                <th class="whitespace">Account Category</th>  
                                                                                <th class="whitespace">Credit Limit</th> 
                                                                                <th class="whitespace">Account Id</th> 
                                                                           </tr><tr>
                                                                                
                                                                                <td class="whitespac">' . (($fnbanking_detail['identity'][0]['name']) ? $fnbanking_detail['identity'][0]['name'] : '-') . '</td>
                                                                                <td class="whitespace">' . (($fnbanking_detail['identity'][0]['address']) ? $fnbanking_detail['identity'][0]['address'] : '-') . '</td>
                                                                                <td class="whitespace">' . (($fnbanking_detail['identity'][0]['ifsc']) ? $fnbanking_detail['identity'][0]['ifsc'] : '-') . '</td>
                                                                                <td class="whitespac">' . (($fnbanking_detail['identity'][0]['micr']) ? $fnbanking_detail['identity'][0]['micr'] : '-') . '</td>
                                                                                <td class="whitespace">' . (($fnbanking_detail['identity'][0]['account_category']) ? $fnbanking_detail['identity'][0]['account_category'] : '-') . '</td>
                                                                                <td class="whitespace">' . (($fnbanking_detail['identity'][0]['credit_limit']) ? $fnbanking_detail['identity'][0]['credit_limit'] : '-') . '</td>
                                                                                <td class="whitespac">' . (($fnbanking_detail['identity'][0]['account_id']) ? $fnbanking_detail['identity'][0]['account_id'] : '-') . '</td>
                                                                           </tr></tbody></table></div>
                                                                           <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Account Reange <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                                                <th class="whitespace">From Date</th> 
                                                                                <th class="whitespace">To Date</th>  
                                                                           </tr><tr>
                                                                                <td class="whitespace">' . (($fnbanking_detail['date_range']['from_date']) ? $fnbanking_detail['date_range']['from_date'] : '-') . '</td>
                                                                                <td class="whitespace">' . (($fnbanking_detail['date_range']['to_date']) ? $fnbanking_detail['date_range']['to_date'] : '-') . '</td>
                                                                           </tr></tbody></table></div>
                                                                           
                                                                           <div class="table-responsive"><h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Bank Valid?  <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4><table class="table table-bordered table-striped"><tbody><tr>                
                                                                                <th class="whitespace">Fraudulent Stmt.</th> 
                                                                                <th class="whitespace"> Stmt. Id</th> 
                                                                                <th class="whitespace">Fraud Type</th> 
                                                                                <th class="whitespace">Account Id</th> 
                                                                                <th class="whitespace">Trans. Hash</th> 
                                                                                <th class="whitespace">Fraud Cate./th> 
                                                                           </tr><tr>
                                                                                <td class="whitespace">' . (($fnbanking_detail['fraud']['fraudulent_statements'][0]) ? $fnbanking_detail['fraud']['fraudulent_statements'][0]: '-') . '</td>
                                                                                <td class="whitespace">' . (($fnbanking_detail['fraud']['fraud_type'][0]['statement_id']) ? $fnbanking_detail['fraud']['fraud_type'][0]['statement_id']: '-') . '</td>
                                                                                <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_type'] : '-') . '</td>
                                                                                <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['account_id'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['account_id'] : '-') . '</td>
                                                                                <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['transaction_hash'] : '-') . '</td>
                                                                                <td class="whitespac">' . (($fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] ) ? $fnbanking_detail['fraud']['fraud_type'][0]['fraud_category'] : '-') . '</td>
                                                                           
                                                                           </tr></tbody></table></div>
                                                                           
                                                                           
                                                  
                                                                                          </div>';
                                                             print $return_data;
                                             }
?>

