<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #9face6);
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        .container {
            padding: 30px;
            background: #ffffff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            max-width: 500px;
        }

        .container h1 {
            font-size: 2.8rem;
            color: #e74c3c;
            margin-bottom: 20px;
        }

        .container p {
            font-size: 1.2rem;
            margin: 10px 0;
            line-height: 1.6;
            color: #555;
        }

        .container img {
            width: 120px;
            margin: 20px 0;
            background-color: #2c3e50;
            padding: 15px;
            border-radius: 7%;
            border: 2px solid #ddd;
        }

        .container a {
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .container a:hover {
            color: #2c3e50;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            .container h1 {
                font-size: 2rem;
            }

            .container img {
                width: 100px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="./public/images/18-BK_kixu8.png" alt="Maintenance Icon">
        <h1>We'll Be Back Soon!</h1>
        <p>Our website is currently undergoing scheduled maintenance. We apologize for any inconvenience.</p>
        <p>Please check back later or contact <a href="mailto:tech@salarywalle.com">tech@salarywalle.com</a> for assistance.</p>
    </div>
</body>

</html>
