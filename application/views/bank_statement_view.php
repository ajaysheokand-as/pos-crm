<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $accountHolder; ?> Bank Statement Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f4f8;
        }

        .dashboard-header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .dashboard-header h1 {
            margin: 0;
        }

        .account-info {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .account-info div {
            flex: 1;
            min-width: 200px;
            margin: 5px;
        }

        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .summary-card {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            flex: 1;
            margin: 0 10px;
            text-align: center;
        }

        .summary-card h3 {
            margin-top: 0;
            color: #2c3e50;
        }

        .summary-card p {
            font-size: 1.5em;
            font-weight: bold;
            margin: 10px 0;
        }

        #filterForm {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        #filterForm input,
        #filterForm select,
        #filterForm button {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        #filterForm button {
            background-color: #2c3e50;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #filterForm button:hover {
            background-color: #34495e;
        }

        .dashboard-content {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .chart-container,
        .transactions-container {
            flex: 1;
            min-width: 300px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .chart-container {
            height: 400px;
        }

        .transactions-container {
            height: 600px;
            overflow-y: auto;
        }

        .transaction-table {
            width: 100%;
            border-collapse: collapse;
        }

        .transaction-table th,
        .transaction-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .transaction-table th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
        }

        .credit {
            color: green;
        }

        .debit {
            color: red;
        }
    </style>
</head>

<body>
    <div class="dashboard-header">
        <h1>Bank Statement Dashboard</h1>
        <div class="account-info">
            <div>
                <strong>Account Holder:</strong> <?php echo $accountHolder; ?>
            </div>
            <div>
                <strong>Account Number:</strong> <?php echo $accountNumber; ?>
            </div>
            <div>
                <strong>Current Balance:</strong> ₹<?php echo number_format($currentBalance, 2); ?>
            </div>
            <div>
                <strong>Statement Period:</strong> <?php echo $startDate; ?> to <?php echo $endDate; ?>
            </div>
        </div>
    </div>

    <div class="summary-cards">
        <div class="summary-card">
            <h3>Total Credits</h3>
            <p id="totalCredits">₹0.00</p>
        </div>
        <div class="summary-card">
            <h3>Total Debits</h3>
            <p id="totalDebits">₹0.00</p>
        </div>
        <div class="summary-card">
            <h3>Net Change</h3>
            <p id="netChange">₹0.00</p>
        </div>
        <div class="summary-card">
            <h3>Avg. Transaction</h3>
            <p id="avgTransaction">₹0.00</p>
        </div>
    </div>

    <form id="filterForm">
        <input type="date" id="startDate" name="startDate" value="<?php echo $startDate; ?>">
        <input type="date" id="endDate" name="endDate" value="<?php echo $endDate; ?>">
        <select id="transactionType">
            <option value="all">All Transactions</option>
            <option value="credit">Credit</option>
            <option value="debit">Debit</option>
        </select>
        <input type="text" id="search" placeholder="Search transactions...">
        <button type="submit">Filter</button>
    </form>

    <div class="dashboard-content">
        <div class="chart-container">
            <canvas id="balanceChart"></canvas>
        </div>
        <div class="transactions-container">
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody id="transactionsBody">
                    <!-- Transactions will be dynamically inserted here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Data passed from PHP
        const transactionData = <?php echo json_encode($groupedTransactions); ?>;
        const monthlyBalances = <?php echo json_encode($monthlyBalances); ?>;

        function flattenTransactions(data) {
            let flatTransactions = [];
            for (const year in data) {
                for (const month in data[year]) {
                    flatTransactions = flatTransactions.concat(data[year][month]);
                }
            }
            return flatTransactions.sort((a, b) => new Date(b.date) - new Date(a.date));
        }

        function populateTransactions(data) {
            const tbody = document.getElementById('transactionsBody');
            tbody.innerHTML = '';
            data.forEach(t => {
                const row = `
                    <tr>
                        <td>${t.date}</td>
                        <td>${t.description}</td>
                        <td class="${t.type}">₹${t.amount.toFixed(2)}</td>
                        <td>${t.type.charAt(0).toUpperCase() + t.type.slice(1)}</td>
                        <td>₹${t.balance.toFixed(2)}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        function updateSummary(data) {
            const totalCredits = data.filter(t => t.type === 'credit').reduce((sum, t) => sum + t.amount, 0);
            const totalDebits = data.filter(t => t.type === 'debit').reduce((sum, t) => sum + t.amount, 0);
            const netChange = totalCredits - totalDebits;
            const avgTransaction = data.reduce((sum, t) => sum + t.amount, 0) / data.length;

            document.getElementById('totalCredits').textContent = `₹${totalCredits.toFixed(2)}`;
            document.getElementById('totalDebits').textContent = `₹${totalDebits.toFixed(2)}`;
            document.getElementById('netChange').textContent = `₹${netChange.toFixed(2)}`;
            document.getElementById('avgTransaction').textContent = `₹${avgTransaction.toFixed(2)}`;
        }

        function createChart(data) {
            const ctx = document.getElementById('balanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        label: 'Monthly Balance',
                        data: Object.values(data),
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Balance (₹)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        }
                    }
                }
            });
        }

        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const transactionType = document.getElementById('transactionType').value;
            const searchTerm = document.getElementById('search').value.toLowerCase();

            const allTransactions = flattenTransactions(transactionData);
            const filteredData = allTransactions.filter(t => {
                const dateMatch = (!startDate || t.date >= startDate) && (!endDate || t.date <= endDate);
                const typeMatch = transactionType === 'all' || t.type === transactionType;
                const searchMatch = t.description.toLowerCase().includes(searchTerm);
                return dateMatch && typeMatch && searchMatch;
            });

            populateTransactions(filteredData);
            updateSummary(filteredData);
            // Note: We're not updating the chart here as it shows monthly balances
        });

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            const allTransactions = flattenTransactions(transactionData);
            populateTransactions(allTransactions);
            updateSummary(allTransactions);
            createChart(monthlyBalances);
        });
    </script>
</body>

</html>
