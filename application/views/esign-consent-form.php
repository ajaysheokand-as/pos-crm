<html>
    <head>
        <title>Consent Form</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"/>
    </head>
    <body>
        <?php
        if (!empty($refstr)) {
            ?>
            <div class='container p-3 mt-3'>

                <div class='row'>
                    <div class='col-12 col-sm-8 bg-light mx-auto p-3 shadow'>
                        <p class='text-center text-primary h3'>
                            Key Fact Statement
                        <p>
                        <div class='table-responsive'>
                            <table class='table table-striped table-bordered'>
                                <thead>
                                    <tr>
                                        <th class="fw-bold h5">S.No.</th>
                                        <th class="fw-bold h5">Parameters</th>
                                        <th class="fw-bold h5">Details</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    ?>
                                    <tr>
                                        <td><b>(I)</b></td>
                                        <td>Name</td>
                                        <td>
                                            <?php
                                            echo $cam_data['first_name'] . ' ' . $cam_data['middle_name'] . ' ' . $cam_data['sur_name'];
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>(II)</b></td>
                                        <td>Loan Amount</td>
                                        <td>&#8377;&nbsp;
                                            <?php
                                            echo $cam_data['loan_recommended'];
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>(III)</b></td>
                                        <td>ROI (in % per day)</td>
                                        <td>
                                            <?php
                                            echo $cam_data['roi'];
                                            ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><b>(IV)</b></td>
                                        <td>Total interest charge during the entire Tenure of the loan</td>
                                        <td>&#8377;&nbsp;
                                            <?php
                                            echo round((($cam_data['loan_recommended'] * $cam_data['roi'] * $cam_data['tenure']) / 100), 0);
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>(V)</b></td>
                                        <td>Processing Fee (Including GST)</td>
                                        <td>&#8377;&nbsp;
                                            <?php
                                            echo round((($cam_data['loan_recommended'] * $cam_data['processing_fee_percent']) / 100), 0);
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>(VI)</b></td>
                                        <td>Insurance charges, if any (in &#8377;)</td>
                                        <td>Nil</td>
                                    </tr>
                                    <tr>
                                        <td><b>(VII)</b></td>
                                        <td>Others (if any) (in &#8377;)</td>
                                        <td>Nil</td>
                                    </tr>
                                    <tr>
                                        <td><b>(VIII)</b></td>
                                        <td>Net disbursed amount</td>
                                        <td>&#8377;&nbsp;
                                            <?php
                                            echo $cam_data['net_disbursal_amount'];
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>(IX)</b></td>
                                        <td>Total amount to be paid by the borrower</td>
                                        <td>&#8377;&nbsp;
                                            <?php
                                            echo $cam_data['repayment_amount'];
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>(X)</b></td>
                                        <td style='width:min-content'>Annual Percentage Rate - Effective annualized interest rate (in %)
                                            (Considering the ROI of <?php echo $cam_data['roi']; ?>% per day)</td>
                                        <td>
                                            <?php
                                            echo $cam_data['roi'] * 365;
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>(XI)</b></td>
                                        <td>Tenure of the Loan (days)</td>
                                        <td>
                                            <?php
                                            echo $cam_data['tenure']
                                            ?>&nbsp;Days
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>(XII)</b></td>
                                        <td>Repayment frequency by the borrower</td>
                                        <td>One Time Only</td>
                                    </tr>
                                    <tr>
                                        <td><b>(XIII)</b></td>
                                        <td>Number of installments of repayment</td>
                                        <td>1</td>
                                    </tr>
                                    <tr>
                                        <td><b>(XIV)</b></td>
                                        <td>Amount of each installment of repayment (in &#8377;)</td>
                                        <td>Nil</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"><p class="fw-bold h4">Details about Contingent Charges</p></td>
                                    </tr>
                                    <tr>
                                        <td><b>(XV)</b></td>
                                        <td>
                                            Rate of annualized penal charges in case of delayed payments (if any)
                                        </td>
                                        <td>
                                            Double the <strong>(III)</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"><p class="fw-bold h4">Other Disclosures</p></td>
                                    </tr>
                                    <tr>
                                        <td><b>(XVI)</b></td>
                                        <td>
                                            Cooling off/look-up period during which borrower shall not be charged any penalty on prepayment of loan  
                                        </td>
                                        <td>
                                            3 Days
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>(XVII)</b></td>
                                        <td>
                                            Name, designation, Address and phone number of nodal grievance
                                            redressal officer designated specifically to deal with FinTech/ digital lending related complaints/ issues
                                        <td>
                                            <p><?= CONTACT_PERSON ?></p>
                                            <p>Mobile: <?= REGISTED_MOBILE ?></p>
                                            <p>Address: <?= REGISTED_ADDRESS ?></p>
                                        </td>
                                    </tr>
                                    <?php
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" checked type="checkbox" value="" id="consent-box">
                            <label class="form-check-label" for="consent-box">
                                I have read and agree to the Key Fact Statement. 
                            </label>
                        </div>
                        <button type='button' class='btn btn-primary' id='btn'>eSign Sanction Letter</button>
                    </div>
                </div>
            </div>

            <?php
        }
        ?>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js'></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <script>

            $("#consent-box").click(function () {
                if (!$(this).is(":checked")) {
                    swal('Please agree to Terms & Conditions');
                }
            });
            $("#btn").click(function () {
                if (!$('#consent-box').is(":checked")) {
                    swal('Please agree to Terms & Conditions');
                } else
                {
                    window.location = "<?= base_url('sanction-esign-request?refstr=' . $refstr . '&consent=true') ?>";
                }
            });

        </script>
    </body>
</html>
