<?php 
$lead_id =$leadDetails->lead_id;
$sql = "SELECT * FROM leads_docs_pending WHERE lead_id = '$lead_id' AND status = 1";
            $result = $this->db->query($sql);
            $docs_data = $result->result();
            //print_r($docs_data);
?>


   <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <tr class="table-primary">
                            <td><input type="checkbox" name="chequeUpload" <?= $leadDetails->lead_document_pending_status == 1 ? 'checked' : '' ?> id="enableCheckbox" onclick="viewEnableHistory('<?= $this->encrypt->encode($leadDetails->lead_id) ?>', this);"> <span>If docs required?</span></td>
                        </tr>
                    </table>
                </div>
    <form id="viewSubmitHistory" method="post" enctype="multipart/form-data" style="float: left;width: 97%;margin:13px 13px 20px 0px;">
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
        <input type="hidden" name="lead_id" id="lead_id" value="<?= $this->encrypt->encode($leadDetails->lead_id) ?>">
        <input type="hidden" name="user_id" id="user_id" value="<?= $_SESSION['isUserSession']['user_id'] ?>">
       
                    <table class="table table-hover table-striped table-bordered">
                        <thead>
                            <tr class="table-primary">
                                <th class="whitespace">Action</th>
                                <th class="whitespace">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="whitespace"><input type="checkbox" name="docsUpload[0]" id="viewchequeUpload" value="Cheque" <?=  $docs_data[0]->status == 1 ? 'checked' : '' ?>> <label for="Cheque">Cheque</label></td>
                                <td class="whitespace"><input type="text" name="reminder_docs[0]" class="form-control" placeholder="Enter Your Requirement." value="<?=  $docs_data[0]->reminder ? $docs_data[0]->reminder : '' ?>" oninput="this.value = this.value.toUpperCase()"></td>
                            </tr>
                            <tr>
                                <td class="whitespace"><input type="checkbox" name="docsUpload[1]" id="viewchequeUpload" value="Bank Statement" <?=  $docs_data[1]->status == 1 ? 'checked' : '' ?>> <label for="Bank Statement">Bank Statement</label></td>
                                <td class="whitespace"><input type="text" name="reminder_docs[1]" class="form-control" placeholder="Enter Your Requirement." value="<?=  $docs_data[1]->reminder ? $docs_data[1]->reminder : '' ?>" oninput="this.value = this.value.toUpperCase()"></td>
                            </tr>
                           
                            <tr>
                                <td class="whitespace"><input type="checkbox" name="docsUpload[2]" id="viewchequeUpload" value="Address Proof" <?=  $docs_data[2]->status == 1 ? 'checked' : '' ?>> <label for="Bank Statement">Address Proof</label></td?>
                                <td class="whitespace"><input type="text" name="reminder_docs[2]" class="form-control" placeholder="Enter Your Requirement."  value="<?=  $docs_data[2]->reminder ? $docs_data[2]->reminder : '' ?>" oninput="this.value = this.value.toUpperCase()"></td>
                            </tr>
                             <tr>
                                <td class="whitespace"><input type="checkbox" name="docsUpload[3]" id="viewchequeUpload" value="Electricity Bill" <?=  $docs_data[3]->status == 1 ? 'checked' : '' ?>> <label for="Electricity Bill">Electricity Bill</label></td>
                                <td class="whitespace"><input type="text" name="reminder_docs[3]" class="form-control" placeholder="Enter Your Requirement." value="<?=  $docs_data[3]->reminder ? $docs_data[3]->reminder : '' ?>" oninput="this.value = this.value.toUpperCase()"></td>
                            </tr>
                            
                            <tr>
                                <td class="whitespace" colspan="3">
                                    <button type="submit" class="btn btn-primary" id="saveDocuments" style="text-align: center; padding-left: 50px; padding-right: 50px; font-weight: bold;">Submit</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>

