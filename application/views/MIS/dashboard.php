<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Dashboard</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            overflow: hidden;
            background: linear-gradient(to bottom, #f4f7f9, #e8eef2);
        }

        #timer {
            float: right;
            border: 2px solid #333;
            border-radius: 50%;
            margin: 20px;
            width: 50px;
            height: 50px;
            text-align: center;
            line-height: 50px;
            font-size: 18px;
            font-weight: bold;
            background: #fff;
            transition: opacity 1s ease-out;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #disbursal_executive_report {
            margin: 10px auto;
            float: inline-end;
            text-align: center;
            width: 90%;
            font-size: 16px;
            background: #fff;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Overlay for screen fade */
        #screen-fade {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0);
            pointer-events: none;
            /* Allow interactions with elements underneath */
            z-index: 1000;
            /* Place above all content */
            transition: background 1s linear;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px auto;
            font-size: 14px;
            background: linear-gradient(to bottom, #ffffff, #f9f9f9);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .grand-total {
            background: linear-gradient(to bottom, #4caf50, #2e7d32);
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .team-header {
            background: linear-gradient(to bottom, #2196f3, #1976d2);
            color: white;
            font-size: 16px;
            font-weight: bold;
        }

        .total-row {
            background: linear-gradient(to bottom, #d4edda, #c3e6cb);
            font-weight: bold;
        }

        th {
            background: linear-gradient(to bottom, #ff8c00, #ff7043);
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .footer-row {
            background: linear-gradient(to bottom, #ffecb3, #ffe082);
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div id="screen-fade"></div>
    <div id="timer">60</div>
    <div id="disbursal_executive_report">
        <p>Loading report...</p>
    </div>

    <script type="text/javascript">
        // Function to fetch and display the report
        async function executive_report() {
            $('#disbursal_executive_report').empty(); // Clear the div before updating

            try {
                // Fetch data using jQuery's AJAX
                const response = await $.ajax({
                    url: '<?= base_url("Admin/DashboardController/sanction_dashboard_report") ?>',
                    type: 'GET',
                });

                if (response) {
                    // Inject raw HTML into the div
                    $('#disbursal_executive_report').html(response);
                } else {
                    console.error('Invalid response:', response);
                    $('#disbursal_executive_report').html('<p>Error: Unable to load report.</p>');
                }
            } catch (error) {
                console.error('AJAX error:', error);
                $('#disbursal_executive_report').html('<p>Error: Unable to load report.</p>');
            }
        }

        // Countdown timer with screen fade effect
        let timeLeft = 300; // Countdown timer in seconds
        const screenFade = document.getElementById('screen-fade');

        function updateTimer() {
            const timer = document.getElementById('timer');

            if (timeLeft <= 0) {
                console.log('Refreshing report...');
                executive_report();
                timeLeft = 300; // Reset the timer
                screenFade.style.background = 'rgba(0, 0, 0, 0)'; // Reset fade
            } else {
                timer.innerHTML = timeLeft;

                // Screen fade effect for the last 5 seconds
                if (timeLeft <= 5) {
                    const fadeOpacity = (10 - timeLeft + 1) * 0.2; // Increment opacity (0.2, 0.4, ..., 1)
                    screenFade.style.background = `rgba(0, 0, 0, ${fadeOpacity})`;
                }

                timeLeft--; // Decrease timer
            }
        }

        // Set interval for countdown
        setInterval(updateTimer, 1000);

        // Initial report fetch
        window.onload = () => {
            setTimeout(() => {
                executive_report();
            }, 2000);
        };
    </script>
</body>

</html>
