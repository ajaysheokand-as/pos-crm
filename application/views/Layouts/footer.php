<html>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        .footer-line {
            width: 100%;
            height: 1px;
            margin: 10px 0px;
            background: #adadad;
        }

        .copyright {
            width: 100%;
            padding: 10px 0px;
            display: flex;
            justify-content: center;
            align-items: center;

        }

        .copyright p {
            margin-bottom: 0px;
            color: white;
        }
    </style>
</head>

<body>
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
        value="<?php echo $this->security->get_csrf_hash(); ?>" />

    <div id="payday_data_modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Data Response</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="payday_model_body"></div>
                </div>
            </div>
        </div>
    </div>


    <div id="achievement_popup"></div>


    <footer>

        <div class="container">

            <div class="row">

                <!--<div class="col-md-5">-->

                <!--    <div class="footer-content">-->

                <!--        <h2>About Company</h2>-->

                <!--        <p>-->
                <!--            <?= COMPANY_NAME ?> is a Non Banking Finance Company registered with RBI. The company uses-->
                <!--            proprietary loan softwares for its various loan offerings to individual customers in a-->
                <!--            completely fintech environment.-->
                <!--        </p>-->

                <!--    </div>-->

                <!--</div>-->

                <!--<div class="col-md-4 footer-soci">-->

                <!--    <div class="footer-content">-->
                <!--        <h2>Address</h2>-->
                <!--        <p>-->
                <!--            <?= REGISTED_ADDRESS ?>.-->
                <!--        </p>-->
                <!--    </div>-->

                <!--</div>-->

                <!--<div class="col-md-3">-->

                <!--    <div class="footer-support">-->

                <!--        <h2>Contact</h2>-->

                <!--        <p><i class="fa-solid fa-phone"></i>&nbsp;&nbsp;tel: +918936222298</p>-->
                <!--        <p><i class="fa-solid fa-mobile"></i>&nbsp;&nbsp; +919876543215</p>-->
                <!--<p><i class="fa fa-envelope"></i>&nbsp;<a href="mailto:<?= TECH_EMAIL ?>"><?= TECH_EMAIL ?></a></p>-->

                <!--<p><i class="fa fa-phone"></i>&nbsp;<a href="tel:+918936962573">+91 89369 62573</a></p>-->

                <!--    </div>-->

                <!--</div>-->

            </div>

            <!--<div class="footer-line"></div>-->
            <div class="copyright">
                <p><i class="fa-solid fa-copyright"></i>&nbsp;Copyright 2025. All Rights Reserved by Aman Fincap Limited.</p>
            </div>
        </div>

    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="<?= base_url('public/front'); ?>/dist/jquery.expander.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>

    <script src="<?= base_url(); ?>public/js/jquery-1.11.2.min.js"></script>
    <!-- <script src="<?= base_url(); ?>public/js/jquery-3.5.1.min.js"></script> -->

    <!-- <script src="<?= base_url(); ?>public/js/jquery-migrate-1.2.1.min.js"></script> -->

    <script src="<?= base_url(); ?>public/js/jRespond.min.js"></script>

    <script src="<?= base_url(); ?>public/js/nav-accordion.js"></script>

    <script src="<?= base_url(); ?>public/js/hoverintent.js"></script>

    <script src="<?= base_url(); ?>public/js/waves.js"></script>

    <script src="<?= base_url(); ?>public/js/switchery.js"></script>

    <script src="<?= base_url(); ?>public/js/jquery.loadmask.js"></script>

    <script src="<?= base_url(); ?>public/js/icheck.js"></script>

    <script src="<?= base_url(); ?>public/js/bootstrap-filestyle.js"></script>

    <script src="<?= base_url(); ?>public/js/bootbox.js"></script>


    <script src="<?= base_url(); ?>public/js/colorpicker.js"></script>

    <script src="<?= base_url(); ?>public/js/bootstrap-datepicker.js"></script>

    <script src="<?= base_url(); ?>public/js/sweetalert.js"></script>

    <script src="<?= base_url(); ?>public/js/moment.js"></script>

    <script src="<?= base_url(); ?>public/js/summernote.min.js"></script>

    <script src="<?= base_url(); ?>public/js/calendar/fullcalendar.js"></script>



    <script src="<?= base_url(); ?>public/js/smart-resize.js"></script>

    <script src="<?= base_url(); ?>public/js/layout.init.js"></script>

    <script src="<?= base_url(); ?>public/js/matmix.init.js"></script>

    <!-- <script src="<?= base_url(); ?>public/js/retina.min.js"></script> -->







    <!--<script src="<?= base_url(); ?>public/front/js/dataTable/dataTable_1.10.25.min.js"></script>

<script src="<?= base_url(); ?>public/front/js/dataTable/dataTable.semantic.min.js"></script>



<script src="<?= base_url(); ?>public/js/jquery.dataTables.js"></script>

<script src="<?= base_url(); ?>public/js/dataTables.tableTools.js"></script>

<script src="<?= base_url(); ?>public/js/dataTables.bootstrap.js"></script>-->

    <script src="<?= base_url(); ?>public/js/bootstrap.min.js"></script>

    <!--<script src="<?= base_url(); ?>public/js/dataTables.responsive.js"></script>-->

    <!--<script src="<?= base_url(); ?>public/js/stacktable.js"></script>-->

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.11/summernote.js"></script> -->

    <!-- <script src="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.7/summernote.js"></script> -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>

    <script src="<?= base_url(); ?>public/front/js/datepicker.min.js"></script>

    <script src="<?= base_url(); ?>public/front/js/jquery.multiselect.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script
        src="https://cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/e8bddc60e73c1ec2475f827be36e1957af72e2ea/src/js/bootstrap-datetimepicker.js"></script>

    <script src="<?= base_url(); ?>public/front/js/ace-responsive-menu.js"></script>

    <script src="<?= base_url(); ?>public/front/js/price-counter/jquery.countup.js"></script>





    <script>
        var csrf_token = $("input[name=csrf_token]").val();
        document.querySelectorAll('#roi, #processing_fee_percent').forEach(input => {
            input.addEventListener('change', e => {
                let el = e.target;
                el.value = Math.min(Math.max(el.value, el.min), el.max);
            });
        });

        $(document).ready(function() {

            $(".togglebtn").click(function() {

                $(".shome").toggle("fast");

            });

        });
    </script>
    <script type="text/javascript">
        loader();

        function loader() {

            $(window).on('load', function() {

                $("#cover").fadeOut(1750);

            });

        }

        $(document).ready(function() {

            $(window).on('load', function() {

                $("#cover").fadeOut(1750);

            });

            $("#respMenu").aceResponsiveMenu({

                resizeWidth: '768', // Set the same in Media query

                animationSpeed: 'fast', //slow, medium, fast

                accoridonExpAll: false //Expands all the accordion menu on click

            });



            $('.counter').each(function() {

                $(this).prop('Counter', 0).animate({

                    Counter: $(this).text()

                }, {

                    duration: 3500,

                    easing: 'swing',

                    step: function(now) {

                        $(this).text(Math.ceil(now));

                    }

                });

            });

        });
    </script>

    <!--counter end-->

    <script type="text/javascript">
        var fullDate = new Date();

        var currentMonth = ((fullDate.getMonth().length + 1) === 1) ? (fullDate.getMonth() + 1) : '0' + (fullDate.getMonth() + 1);

        var fullDate = fullDate.getFullYear() + "-" + currentMonth + "-" + fullDate.getDate();

        // var valid_age =  fullDate.getFullYear() - 20 + "-" + currentMonth + "-" + fullDate.getDate();

        // var valid_start_age =  fullDate.getFullYear() - 60 + "-" + currentMonth + "-" + fullDate.getDate();

        // console.log("currentMonth : " +currentMonth);



        $(document).ready(function() {

            $("#dob, DOB, #dateOfJoining, #date_of_recived, #dob, #employedSince,#p_dob").keypress(function myfunction(event) {

                var regex = new RegExp("^[0-9?=.*!@#$%^&*]+$");

                var key = String.fromCharCode(event.charCode ? event.which : event.charCode);

                if (!regex.test(key)) {

                    event.preventDefault();

                    return false;

                }

                return false;

            });

            $("#dob, #dateOfJoining, #date_of_recived,#p_dob").datepicker({
                format: 'dd-mm-yyyy',
                todayHighlight: true,
                autoclose: true,
                // startView: 2,
                endDate: new Date()
            });

            $("#employedSince, #residenceSince").datepicker({
                format: 'dd-mm-yyyy',
                todayHighlight: true,
                autoclose: true,
                startView: 2,
                //            viewMode: "months",
                //            minViewMode: "months",
                endDate: new Date()
            });
            var currentM = '';
            $('#salary_credit1').change(function() {
                currentM = $(this).val();
                console.log(currentM);
            });

            $("#DOB,#dob,#p_dob").datepicker({

                format: 'dd-mm-yyyy',

                todayHighlight: true,

                autoclose: true,

                // startView: 2,

                startDate: '-60y',

                endDate: '-19y'

            });


            $("#holiday_date").datepicker({

                format: 'dd-mm-yyyy',

                todayHighlight: true,

                autoclose: true,

                // startView: 2,

                startDate: '0d',
                //
                //            endDate: '-19y'

            });



            $("#cc_statementDate").datepicker({

                format: 'dd-mm-yyyy',

                todayHighlight: true,

                autoclose: true,

                startDate: '-30d',

                endDate: new Date()

            });



            $("#cc_paymentDueDate, #repayment_date").datepicker({
                format: 'dd-mm-yyyy',
                todayHighlight: true,
                autoclose: true,
                startDate: new Date(),
                endDate: '+90d'
            });
            $("#next_pay_date").datepicker({
                format: 'dd-mm-yyyy',
                todayHighlight: true,
                autoclose: true,
                startDate: new Date(),
                endDate: '+60d'
            });



            $("#disbursal_date").datepicker({

                format: 'dd-mm-yyyy',

                todayHighlight: true,

                autoclose: true,

                startDate: '-5d',

                endDate: '+2d'

            });

        });



        $("#to_date, .SearchForExport").prop('disabled', true);

        $("#from_date").datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true,
            // startView: 2,
            //        startDate: '-180d',
            endDate: new Date()
        });

        // startDate: '-30d',

        $("#from_date").change(function() {
            var from_date = $(this).val();
            $("#to_date").prop('disabled', false);
            $("#to_date").datepicker({
                format: 'dd-mm-yyyy',
                todayHighlight: true,
                autoclose: true,
                // startView: 2,
                startDate: from_date,
                endDate: '+31d'
            });
        });
    </script>



    <script>
        $(document).ready(function() {

            $("#mobile, #alternateMobileNo, #refrence1mobile, #enterAltMobileOTP").keypress(function(e) {

                $('#errormobile').html('');

                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {

                    $('#errormobile').html('Number Only!').show().css({
                        'color': 'red'
                    }).fadeOut('slow');

                    return false;

                }

                if ($(this).val().length >= 10) {

                    $('#errormobile').html('Verified Mobile!').show().css({
                        'color': 'green'
                    });

                    return false;

                }

                if ($(this).val().length < 9) {

                    $('#errormobile').html('Mobile 10 digit required!').show().css({
                        'color': 'red'
                    });

                } else {

                    $('#errormobile').html('Verified Mobile!').show().css({
                        'color': 'green'
                    });



                }

            });



            $("#pincode, #pincode1, #pincode2, #pincode3, #yourPincode").keypress(function(e) {

                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {

                    return false;

                }

                if ($(this).val().length >= 6) {

                    return false;

                }

                if ($(this).val().length < 5) {

                    $('#errorpincode').html('Pincode 6 digit required!').show().css({
                        'color': 'red'
                    });

                } else {

                    $('#errorpincode').html('Verified Pincode!').show().css({
                        'color': 'green'
                    });



                }

            });



            // number only



            $("#higherDPDLast3month, #loan_recomended, #processing_fee, #cc_outstanding, #cc_limit").keypress(function(e) {

                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {

                    return false;

                }

            });

            $("#roi, #loan_applied, #loan_tenure, #obligations").keypress(function(e) {
                var val = $(this).val();
                var regex = /^(\+|-)?(\d*\.?\d*)$/;
                if (regex.test(val + String.fromCharCode(e.charCode))) {
                    return true;
                }
                return false;
            });

            $("#bankA_C_No, #confBankA_C_No").keypress(function(e) {
                var val = $(this).val();
                var regex = /^(\+|-)?(\d*\.?\d*)$/;
                if (regex.test(val + String.fromCharCode(e.charCode))) {
                    return true;
                }

                var regex_alpha = /^[A-Za-z ]+$/;
                if (regex_alpha.test(val + String.fromCharCode(e.charCode))) {
                    $(this).val("");
                    return false;
                }
            });




            // alpha only



            $('input[type=text], select').keyup(function() {
                $(this).val($(this).val().toUpperCase());
            });

            $('textarea').focusout(function() {
                $(this).val($(this).val().toUpperCase());
            });



            $("#first_name, #middle_name, #sur_name, #customer_name, #special_approval, #bankHolder_name, #refrence1").keypress(function(event) {
                var inputValue = event.which;
                if (!(inputValue >= 65 && inputValue <= 122) && (inputValue != 32 && inputValue != 0)) {
                    event.preventDefault();
                }
            });
        });

        function IsEmail(email) {
            var regex = /([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/;
            let valid_email = $(email).val();
            if (valid_email.match(regex)) {
                return true;
            } else {
                $(email).val("").focus();
                return false;
            }
        }

        function IsOfficialEmail(email) {
            let valid_email = $(email).val();
            var re = /.+@(loanwalle)\.com$/;
            var validEmail = re.test(valid_email);
            if (validEmail == true) {
                $("#emailErr").html("");
                return true;
            } else {
                $(email).val("");
                $("#emailErr").html("Acceptable domain name '@loanwalle.com'").css('color', 'red');
                return false;
            }

        }



        function validatePanNumber(pan) {
            let pannumber = $(pan).val();
            var regex = /[a-zA-z]{5}\d{4}[a-zA-Z]{1}/;
            if (pannumber.length == 10) {
                if (pannumber.match(regex)) {
                    $(pan).css('border-color', 'lightgray');
                } else {
                    $(pan).val("").focus().css('border-color', 'red');
                    return false;
                }
            } else {
                $(pan).val("").focus().css('border-color', 'red');
                return false;
            }
        }
    </script>

    <script src="<?= base_url(); ?>public/js/toast_notification/flash.min.js"></script>

    <script type="text/javascript">
        $("#salary_credit1_date, #salary_credit2_date, #salary_credit3_date, #sfd, #sed").datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            changeMonth: true,
            autoclose: true,
            // minDate: m,
            // startView: 2,
            // viewMode: "months",
            // minViewMode: "months",
            // startMonth : m,
            // endMonth : m
        });


        function SalaryCredit(month) {
            var m = $(month).val();
            console.log(m);

            // $("#salary_credit1_date, #salary_credit2_date, #salary_credit3_date").datepicker({
            //     format: 'dd-mm',
            //     todayHighlight: true,
            //     changeMonth: true,
            //     autoclose: true,
            //     minDate: m,
            //     // startView: 2,
            //     // viewMode: "months",
            //     // minViewMode: "months",
            //     // startMonth : m,
            //     // endMonth : m
            // });
        }

        function catchSuccess(success) {

            $('<audio id="chatAudio"><source src="<?= base_url() ?>public/ringtone/success.mp3" type="audio/ogg"><source src="<?= base_url() ?>public/ringtone/success.mp3" type="audio/mpeg"></audio>').appendTo('body');

            // $('#chatAudio')[0].play();

            flash(success, {
                'bgColor': '#2d6f36'
            });

        }

        function catchError(error) {

            $('<audio id="chatAudio"><source src="<?= base_url() ?>public/ringtone/success.mp3" type="audio/ogg"><source src="<?= base_url() ?>public/ringtone/success.mp3" type="audio/mpeg"></audio>').appendTo('body');

            // $('#chatAudio')[0].play();

            flash(error, {
                'bgColor': '#C0392B'
            });

        }

        function catchNotification(notify) {

            $('<audio id="chatAudio"><source src="<?= base_url() ?>public/ringtone/success.mp3" type="audio/ogg"><source src="<?= base_url() ?>public/ringtone/success.mp3" type="audio/mpeg"></audio>').appendTo('body');

            // $('#chatAudio')[0].play();

            flash(notify, {
                'bgColor': '#d4ac1a'
            })
        }

        function tenure(t) {
            var val = $(t).val();
            if (val <= 0 || val > 90) {
                $(t).val('');
                $(t).attr('placeholder', 'Tenure should be between 1 to 90 days');
            }
        }

        function monthlyIncome(t) {
            var val = $(t).val();
            if (val.length < 5) {
                $(t).val('');
                $(t).attr('placeholder', 'Monthly Salary should be 10000 minimum');
            }
        }



        //    var _gaq = _gaq || [];
        //
        //    _gaq.push(['_setAccount', 'UA-36251023-1']);
        //
        //    _gaq.push(['_setDomainName', 'jqueryscript.net']);
        //
        //    _gaq.push(['_trackPageview']);
        //
        //
        //
        //    (function () {
        //
        //        var ga = document.createElement('script');
        //        ga.type = 'text/javascript';
        //        ga.async = true;
        //
        //        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        //
        //        var s = document.getElementsByTagName('script')[0];
        //        s.parentNode.insertBefore(ga, s);
        //
        //    })();



        function defaultLoginRole(user_id, role_id) {
            if (confirm("Are You Sure to change your role.")) {
                var role_id = $(role_id).val();
                $.ajax({
                    url: '<?= base_url("defaultLoginRole/") ?>' + user_id,
                    type: 'POST',
                    dataType: "json",
                    async: false,
                    data: {
                        role_id: role_id,
                        csrf_token
                    },
                    success: function(response) {
                        if (response.errSession) {
                            window.location.href = "<?= base_url() ?>";
                        } else if (response.msg != undefined) {
                            window.location.href = "<?= base_url('dashboard') ?>";
                        } else {
                            alert(response.err);
                            window.location.href = "<?= base_url('logout') ?>";
                        }
                    }
                });
            }
        }

        let currentUserRole = "<?php echo $_SESSION['isUserSession']['role_id']; ?>";

        function checkRoleChange(role_id) {
            if (role_id !== currentUserRole) {
                currentUserRole = role_id;
                triggerReloadForAllTabs();
            }
        }

        function triggerReloadForAllTabs() {
            localStorage.setItem('reloadTabs', 'true'); // Set flag to true
        }

        window.addEventListener('storage', function(event) {
            if (event.key === 'reloadTabs' && event.newValue === 'true') {
                // Reload the page
                location.reload();
                // Reset the flag so that reload does not trigger repeatedly
                localStorage.setItem('reloadTabs', 'false');
            }
        });
    </script>
    <!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script> -->
</body>

</html>
