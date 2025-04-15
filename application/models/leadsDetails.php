<style type="text/css">
    table{
        width: 100%;
    }
    table tr th, td{
        width: 25%;
    }
    table th{
        font-weight: bold;
    }
</style>
<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered">
        <tr>
            <th>Lead ID</th>
            <td><?= ($leadDetails->lead_id) ? $leadDetails->lead_id : '-' ?></td>
            <th>Lead Reference No</th>
            <td><?= ($leadDetails->lead_reference_no) ? $leadDetails->lead_reference_no : '-' ?></td>
        </tr>

        <tr>
            <th>Application No.</th>
            <td><?= ($leadDetails->application_no) ? $leadDetails->application_no : '-' ?></td>
            <th>CIF No.</th>
            <td><?= ($leadDetails->customer_id) ? $leadDetails->customer_id : '-' ?></td>
        </tr>
        <tr>
            <th>Borrower Type</th>
            <td><?= ($leadDetails->user_type) ? strtoupper($leadDetails->user_type) : '-' ?></td>
            <th>PAN</th>
            <td><?= ($leadDetails->pancard) ? strtoupper($leadDetails->pancard) : '-' ?></td>
        </tr>
        <tr>
            <th>Loan Applied</th>
            <td><?= ($leadDetails->loan_amount) ? round($leadDetails->loan_amount) : '-' ?></td>
            <th>Loan Tenure</th>
            <td><?= ($leadDetails->tenure != 0 && $leadDetails->tenure != '') ? $leadDetails->tenure : '-' ?></td>
        </tr>
        <tr>
            <th>Loan Purpose</th>
            <td><?= ($leadDetails->purpose) ? strtoupper($leadDetails->purpose) : '-' ?></td>
            <th>First Name</th>
            <td><?= ($leadDetails->first_name) ? strtoupper($leadDetails->first_name) : '-' ?></td>
        </tr>
        <tr>
            <th>Middle Name</th>
            <td><?= ($leadDetails->middle_name) ? strtoupper($leadDetails->middle_name) : '-' ?></td>
            <th>Surname</th>
            <td><?= ($leadDetails->sur_name) ? strtoupper($leadDetails->sur_name) : '-' ?></td>
        </tr>
        <tr>
            <th>Gender</th>
            <td><?= ($leadDetails->gender) ? strtoupper($leadDetails->gender) : '-' ?></td>
            <th>DOB</th>
            <td><?= ($leadDetails->dob) ? date('d-m-Y', strtotime($leadDetails->dob)) : '-' ?></td>
        </tr>
        <tr>
            <th>Father's Name</th>
            <td><?= ($leadDetails->father_name) ? strtoupper($leadDetails->father_name) : '-' ?></td>
            <th>Religion</th>
            <td><?= ($leadDetails->religion_name) ? strtoupper($leadDetails->religion_name) : '-' ?></td>
        </tr>
        <tr>
            <th>Income Type</th>
            <td><?= (($leadDetails->income_type == 1) ? 'SALARIED' : (($leadDetails->income_type == 2) ? 'SELF-EMPLOYED' : '-')) ?></td>
            <th>Salary Mode</th>
            <td><?= ($leadDetails->salary_mode) ? $leadDetails->salary_mode : '-' ?></td>
        </tr>
        <tr>
            <th>Salary</th>
            <td><?= ($leadDetails->monthly_income) ? round($leadDetails->monthly_income) : '-' ?></td>
            <th>Obligations</th>
            <td><?= ($leadDetails->obligations) ? round($leadDetails->obligations) : '-' ?></td>
        </tr>



        <tr>
            <th>State</th>
            <td><?= ($leadDetails->m_state_name) ? strtoupper($leadDetails->m_state_name) : '-' ?></td>
            <th>City</th>
            <td><?= ($leadDetails->m_city_name) ? strtoupper($leadDetails->m_city_name) : '-' ?></td>
        </tr>
        <tr>
            <th>Pincode</th>
            <td><?= ($leadDetails->pincode) ? $leadDetails->pincode : '-' ?></td>
            <th>Branch</th>
            <td><?= ($leadDetails->m_branch_name) ? $leadDetails->m_branch_name : '-' ?></td>
        </tr>
        <tr>
            <th>Mobile</th>
            <td>
                <a href="tel:<?= $leadDetails->mobile ?>">
                    <i class="fa fa-phone"></i>
                </a>&nbsp;<?= ($leadDetails->mobile) ? $leadDetails->mobile : '-' ?>&nbsp;

                <?php if (!empty($leadDetails->mobile) && ((agent == 'CR1' && !empty($leadDetails->lead_screener_assign_user_id) && $leadDetails->lead_screener_assign_user_id == user_id) || (agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id)) && in_array($leadDetails->stage, array("S2", "S3", "S5", "S6", "S11"))) { ?>
                    <button onclick="Click_To_Call(<?= $leadDetails->lead_id ?>, 1, 1)">RUNO Call Assign</button>
                <?php } else if (!empty($leadDetails->mobile) && in_array(agent, ['CR1', 'CR2']) && in_array($leadDetails->lead_status_id, [14, 19])) { ?>
                    <button onclick="Click_To_Call(<?= $leadDetails->lead_id ?>, 1, 2)">RUNO Call Assign</button>
                <?php } else if (!empty($leadDetails->mobile) && in_array(agent, ['CO1', 'CO2', 'CO3']) && in_array($leadDetails->lead_status_id, [14, 19])) { ?>
                    <button onclick="Click_To_Call(<?= $leadDetails->lead_id ?>, 1, 3)">RUNO Call Assign</button>
                <?php } ?>
            </td>

            <th>Mobile Alternate</th>

            <td>
                <a href="tel:<?= $leadDetails->alternate_mobile ?>">
                    <i class="fa fa-phone"></i>
                </a>&nbsp;<?= ($leadDetails->alternate_mobile) ? $leadDetails->alternate_mobile : '-' ?>&nbsp;

                <?php if (!empty($leadDetails->alternate_mobile) && (((agent == 'CR1' && !empty($leadDetails->lead_screener_assign_user_id) && $leadDetails->lead_screener_assign_user_id == user_id) || (agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id))) && in_array($leadDetails->stage, array("S2", "S3", "S5", "S6", "S11"))) { ?>
                    <button onclick="Click_To_Call(<?= $leadDetails->lead_id ?>, 2, 1)">RUNO Call Assign</button>
                <?php } else if (!empty($leadDetails->alternate_mobile) && in_array(agent, ['CR1', 'CR2']) && in_array($leadDetails->lead_status_id, [14, 19])) { ?>
                    <button onclick="Click_To_Call(<?= $leadDetails->lead_id ?>, 2, 2)">RUNO Call Assign</button>
                <?php } else if (!empty($leadDetails->alternate_mobile) && in_array(agent, ['CO1', 'CO2', 'CO3']) && in_array($leadDetails->lead_status_id, [14, 19])) { ?>
                    <button onclick="Click_To_Call(<?= $leadDetails->lead_id ?>, 2, 3)">RUNO Call Assign</button>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>Email (Personal)</th>
            <td><a href="mailto:<?= $leadDetails->email ?>"><i class="fa fa-envelope"></i></a>&nbsp;<?= ($leadDetails->email) ? $leadDetails->email : '-' ?></td>
            <th>Email (Office)</th>
            <td><a href="mailto:<?= $leadDetails->alternate_email ?>"><i class="fa fa-envelope"></i></a>&nbsp;<?= ($leadDetails->alternate_email) ? $leadDetails->alternate_email : '-' ?></td>
        </tr>
        <tr>
            <th>Lead Source</th>
            <td><?= (isset($master_data_source[$leadDetails->lead_data_source_id])) ? strtoupper($master_data_source[$leadDetails->lead_data_source_id]) : '-' ?></td>
            <th>UTM Source</th>
            <td><?= ($leadDetails->utm_source) ? $leadDetails->utm_source : '-' ?></td>
            <!--<th>Geo Coordinates</th>-->
            <!--<td><?= ($leadDetails->coordinates) ? $leadDetails->coordinates : '-' ?></td>-->
        </tr>
        <tr>
            <th>Applied On</th>
            <td><?= ($leadDetails->created_on) ? date('d-m-Y H:i:s', strtotime($leadDetails->created_on)) : '-' ?></td>
            <th>UTM Campaign</th>
            <td><?= ($leadDetails->utm_campaign) ? $leadDetails->utm_campaign : '-' ?></td>
        </tr>
        <tr>
            <th>IP Address</th>
            <td><?= ($leadDetails->ip) ? $leadDetails->ip : '-' ?></td>
            <th>Status</th>
            <td><?= ($leadDetails->status) ? $leadDetails->status : '-' ?></td>
        </tr>
        <tr>
            <th>Marital Status</th>
            <td><?= ($leadDetails->m_marital_status_name) ? $leadDetails->m_marital_status_name : '-' ?></td>
            <th>Spouse Name</th>
            <td><?= ($leadDetails->customer_spouse_name) ? $leadDetails->customer_spouse_name : '-' ?></td>
        </tr>

        <tr>
            <th>Spouse Occupation </th>
            <td><?= ($leadDetails->customer_spouse_occupation_id) ? $leadDetails->customer_spouse_occupation_id : '-' ?></td>
            <th>Qualification </th>
            <td><?= ($leadDetails->customer_qualification_id) ? $leadDetails->customer_qualification_id : '-' ?></td>
        </tr>

        <?php if (agent == "CR1") { ?>
            <tr>
                <th>Appointment DateTime</th>
                <td><?= !empty($leadDetails->customer_appointment_schedule) ? date("d-m-Y h:i A", strtotime($leadDetails->customer_appointment_schedule)) : '-' ?></td>
                <th>Appointment Remarks</th>
                <td><?= !empty($leadDetails->customer_appointment_remark) ? $leadDetails->customer_appointment_remark : '-' ?></td>
            </tr>
        <?php } ?>
        <tr>
            <th colspan="4">
                <input type="checkbox" id="tnc" name="tnc" class="lead-checkbox2"<?= ($leadDetails->term_and_condition == "YES") ? "checked" : 'unchecked' ?> disabled>&nbsp;
                I agree to <?= BRAND_NAME ?>'s Terms and Conditions and Privacy Policy and receive communication from <?= BRAND_NAME ?> via SMS, Email and Whatsapp.
            </th>
        </tr>
    </table>
</div>
