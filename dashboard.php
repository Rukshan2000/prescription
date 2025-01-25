<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'pharmacy');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query for total income
$sql_total_income = "SELECT SUM(od.quantity * m.price) AS total_income
                     FROM medicine m
                     JOIN order_details od ON m.name = od.medicine_name";
$result_total_income = $conn->query($sql_total_income);
$total_income = $result_total_income->fetch_assoc()['total_income'];

// Query for total orders
$sql_total_orders = "SELECT COUNT(*) AS total_orders FROM orders";
$result_total_orders = $conn->query($sql_total_orders);
$total_orders = $result_total_orders->fetch_assoc()['total_orders'];

// Query for total quantity sold
$sql_total_qty = "SELECT SUM(od.quantity) AS total_qty_sold
                  FROM order_details od";
$result_total_qty = $conn->query($sql_total_qty);
$total_qty_sold = $result_total_qty->fetch_assoc()['total_qty_sold'];

// Query for order status distribution
$sql_order_status = "SELECT order_status, COUNT(*) AS status_count 
                     FROM orders 
                     GROUP BY order_status";
$result_order_status = $conn->query($sql_order_status);

// Collect order statuses for chart
$order_statuses = [];
$status_counts = [];
while ($row = $result_order_status->fetch_assoc()) {
    $order_statuses[] = $row['order_status'];
    $status_counts[] = $row['status_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">



    <div class="container mx-auto p-6">
        <!-- Dashboard Header -->
        <div class="flex justify-between mb-6 items-center">
            <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
            <a href="admin_login.php">
    <button class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400">
        Logout
    </button>
</a>
        </div>

        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Income Card -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition duration-300 flex items-center">
                <div class="flex-shrink-0 bg-indigo-600 text-white p-4 rounded-full">
                    <i class="fas fa-dollar-sign text-3xl"></i>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold text-gray-800">Total Income</h2>
                    <p class="text-xl text-gray-600"><?= number_format($total_income, 2) ?></p>
                </div>
            </div>

            <!-- Total Orders Card -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition duration-300 flex items-center">
                <div class="flex-shrink-0 bg-green-600 text-white p-4 rounded-full">
                    <i class="fas fa-box text-3xl"></i>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold text-gray-800">Total Orders</h2>
                    <p class="text-xl text-gray-600"><?= number_format($total_orders) ?></p>
                </div>
            </div>

            <!-- Total Quantity Sold Card -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition duration-300 flex items-center">
                <div class="flex-shrink-0 bg-yellow-600 text-white p-4 rounded-full">
                    <i class="fas fa-cogs text-3xl"></i>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold text-gray-800">Total Quantity Sold</h2>
                    <p class="text-xl text-gray-600"><?= number_format($total_qty_sold) ?></p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Order Status Pie Chart -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition duration-300">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Order Status Distribution</h3>
                <canvas id="orderStatusChart"></canvas>
            </div>

            <!-- Total Income Bar Chart -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition duration-300">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Income by Order</h3>
                <canvas id="incomeChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Order Status Pie Chart
        const ctx1 = document.getElementById('orderStatusChart').getContext('2d');
        const orderStatusChart = new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: <?= json_encode($order_statuses) ?>,
                datasets: [{
                    data: <?= json_encode($status_counts) ?>,
                    backgroundColor: ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#9966ff'],
                }]
            }
        });

        // Total Income Bar Chart
        const ctx2 = document.getElementById('incomeChart').getContext('2d');
        const incomeChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?= json_encode($order_statuses) ?>,
                datasets: [{
                    label: 'Income by Order Status',
                    data: <?= json_encode($status_counts) ?>,
                    backgroundColor: '#36a2eb',
                    borderColor: '#36a2eb',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</body>
</html>

<?php $conn->close(); ?>
