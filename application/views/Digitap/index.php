<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aadhaar Verification</title>
    <link rel="stylesheet preload" href="<?= base_url('public'); ?>/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #278B65;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 506px;
            max-width: 90%;
            margin: auto;
            padding: 30px;
            text-align: center;
        }

        .container h1 {
            font-size: 24px;
            font-weight: bold;
            color: #278B65;
            margin-bottom: 15px;
        }

        .container p {
            font-size: 16px;
            color: #333;
            margin-bottom: 25px;
        }

        .container input {
            background-color: #eee;
            border: none;
            margin: 10px 0;
            padding: 10px 15px;
            font-size: 14px;
            border-radius: 8px;
            width: 80%;
            outline: none;
        }

        .container button,
        .container a {
            background-color: #278B65;
            color: #fff;
            font-size: 14px;
            padding: 10px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .container button:hover,
        .container a:hover {
            background-color: #183c73;
        }

        .thank-you-container {
            /*display: none;*/
        }

        .tick-icon-container {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #28a745;
            /* Light green background */
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
        }

        .tick-icon {
            font-size: 50px;
            color: #fff;
            /* Green tick color */
        }
    </style>
</head>

<body>
    <?php
    $csrf = array(
        'name' => $this->security->get_csrf_token_name(),
        'hash' => $this->security->get_csrf_hash()
    );
    ?>

    <!-- Aadhaar Form -->
    <div class="container" id="aadhaarForm">
        <div class="logo_container">
            <a href="https://paisaonsalary.com/" target="_blank">
                <!-- <img src="https://sot-website.s3.ap-south-1.amazonaws.com/upload/company_logo.png" alt="logo" style="width: 50%; margin-bottom: 20px;"> -->
                <img src="https://lms.paisaonsalary.in/public/images/final_logo.png" alt="logo" style="width: 50%; margin-bottom: 20px;">
            </a>
        </div>
        <h1>Fill Aadhaar Number</h1>
        <p>Please enter your Aadhaar number to proceed.</p>
        <input type="hidden" name="<?= $csrf['name']; ?>" value="<?= $csrf['hash']; ?>" />
        <input type="hidden" name="lead_id" id="lead_id" value="<?= $leadDetails->lead_id; ?>">
        <input type="text" name="aadhaar_no" id="aadhaar_no" placeholder="Enter Aadhaar Number" title="Aadhaar Number" required style="text-align: center;" onpaste="return false;">
        <button type="button" id="request_otp">Get OTP</button>
    </div>

    <!-- OTP Form -->
    <div class="container" id="otpForm" style="display: none;">
        <div class="logo_container">
            <a href="https://paisaonsalary.com/" target="_blank">
                <!-- <img src="https://sot-website.s3.ap-south-1.amazonaws.com/upload/company_logo.png" alt="logo" style="width: 50%; margin-bottom: 20px;"> -->
                <img src="https://lms.paisaonsalary.in/public/images/final_logo.png" alt="logo" style="width: 50%; margin-bottom: 20px;">
            </a>
        </div>
        <p id="seccess_msg" style="color: green; font-weight: bold;"></p>
        <h1>Enter OTP</h1>
        <p>Please enter the OTP sent to your Aadhaar-registered mobile.</p>
        <input type="hidden" name="<?= $csrf['name']; ?>" value="<?= $csrf['hash']; ?>" />
        <input type="hidden" name="lead_id" value="<?= $leadDetails->lead_id; ?>">
        <input type="text" name="otp" id="otp" placeholder="Enter OTP" title="OTP" required style="text-align: center;" onpaste="return false;">
        <button type="button" id="submit_otp">Submit OTP</button>
    </div>

    <!-- Thank You Section -->
    <div class="container thank-you-container" id="thankYouSection">
        <div class="tick-icon-container">
            <div class="tick-icon">✔</div> <!-- Green Tick -->
        </div>
        <div class="logo_container">
            <a href="https://paisaonsalary.com/" target="_blank">
                <!-- <img src="https://sot-website.s3.ap-south-1.amazonaws.com/upload/company_logo.png" alt="logo" style="width: 50%; margin-bottom: 20px;"> -->
                <img src="https://lms.paisaonsalary.in/public/images/final_logo.png" alt="logo" style="width: 50%; margin-bottom: 20px;">
            </a>
        </div>
        <h1>Thank You!</h1>
        <p>Your Aadhaar verification was successful. You can now proceed with the next steps.</p>
        <a href="<?= WEBSITE_URL; ?>" target="_blank">Go to Website</a>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $("#aadhaar_no").on("input", function() {
            var value = $(this).val().replace(/\D/g, '');
            if (value.length > 12) {
                value = value.slice(0, 12);
            }
            $(this).val(value);
        });

        $("#otp").on("input", function() {
            var value = $(this).val().replace(/\D/g, '');
            if (value.length > 6) {
                value = value.slice(0, 6);
            }
            $(this).val(value);
        });

        $(document).ready(function() {
            const csrf_token_name = "<?= $this->security->get_csrf_token_name(); ?>";
            const csrf_token_value = "<?= $this->security->get_csrf_hash(); ?>";

            $('#request_otp').click(function() {
                const aadhaar_no = $('#aadhaar_no').val();
                const lead_id = $('#lead_id').val();

                if (!aadhaar_no) {
                    alert("Please enter your Aadhaar number.");
                    return;
                }

                $.ajax({
                    url: "<?= base_url('verifyEkycDigitap') ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        lead_id: lead_id,
                        aadhaar_no: aadhaar_no,
                        [csrf_token_name]: csrf_token_value
                    },
                    beforeSend: function() {
                        $('#request_otp').prop('disabled', true).text('Processing...');
                    },
                    success: function(response) {
                        if (response.err) {
                            alert("Error: " + response.err);
                        } else {
                            $('#aadhaarForm').hide();
                            $('#otpForm').show();
                            $('#seccess_msg').text(response.success);
                        }
                    },
                    complete: function() {
                        $('#request_otp').prop('disabled', false).text('Get OTP');
                    }
                });
            });

            $('#submit_otp').click(function() {
                const otp = $('#otp').val();
                const lead_id = $('#lead_id').val();

                if (!otp) {
                    alert("Please enter the OTP.");
                    return;
                }

                $.ajax({
                    url: "<?= base_url('ekyc_otp_verify') ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        lead_id: lead_id,
                        otp: otp,
                        [csrf_token_name]: csrf_token_value
                    },
                    beforeSend: function() {
                        $('#submit_otp').prop('disabled', true).text('Verifying...');
                    },
                    success: function(response) {
                        if (response.err) {
                            alert("Error: " + response.err);
                        } else {
                            $('#otpForm').hide();
                            $('#thankYouSection').show();
                        }
                    },
                    complete: function() {
                        $('#submit_otp').prop('disabled', false).text('Submit OTP');
                    }
                });
            });
        });
    </script>
</body>

</html>
