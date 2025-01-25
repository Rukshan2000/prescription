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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>

<body class="bg-gray-50">

    <!-- Side Navigation -->
    <div class="w-64 bg-gray-800 text-white h-screen px-4 py-8 fixed  top-0">
        <h1 class="text-2xl font-semibold text-center mb-6">Admin Panel</h1>
        <ul class="flex-grow">
            <!-- Dashboard Section -->
            <li>
                <a href="dashboard.php" class="block py-2 px-4 hover:bg-gray-700 rounded-md flex items-center">
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Orders Section -->
            <li>
                <a href="admin.php" class="block py-2 px-4 hover:bg-gray-700 rounded-md flex items-center">
                    <span>Orders</span>
                </a>
            </li>

            
        <li>
            <a href="medicine.php" class="block py-2 px-4 hover:bg-gray-700 rounded-md flex items-center">
                <span>Medicines</span>
            </a>
        </li>

            <li>
                <a href="rejected.php" class="block py-2 px-4 hover:bg-gray-700 rounded-md flex items-center">
                    <span>Rejected Orders</span>
                </a>
            </li>
            <li>
                <a href="verify_doctor.php" class="block py-2 px-4 hover:bg-gray-700 rounded-md flex items-center">
                    <span>Doctor Verification</span>
                </a>
            </li>

            <li>
                <a href="create_admin.php" class="block py-2 px-4 hover:bg-gray-700 rounded-md flex items-center">
                    <span>Create Admin</span>
                </a>
            </li>

            <!-- Reports Section -->
            <li>
                <a href="javascript:void(0)" onclick="toggleSubNav('rejectedOrdersSubNav', this)"
                    class="block py-2 px-4 hover:bg-gray-700 rounded-md flex items-center">
                    <span>Reports</span>
                    <i class="fas fa-chevron-down ml-2"></i>
                </a>
                <ul id="rejectedOrdersSubNav" class="ml-4 hidden">
                    <li><a href="medicine_sales_report.php"
                            class="block py-2 px-4 hover:bg-gray-600 rounded-md">Medicine sales Report</a></li>
                    <li><a href="order_report.php" class="block py-2 px-4 hover:bg-gray-600 rounded-md">Order Report</a>
                    </li>
                    <li><a href="inventory_report.php" class="block py-2 px-4 hover:bg-gray-600 rounded-md">Inventory
                            Report</a></li>
                </ul>
            </li>
        </ul>

        <!-- Logout Button -->
        <div class="mt-48">
            <a href="admin_login.php">
                <button
                    class="w-full bg-red-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-400">
                    Logout
                </button>
            </a>
        </div>
    </div>


    <!-- Add Font Awesome CDN -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>

    <script>
        // Function to toggle the visibility of sub-navigation and change arrow direction
        function toggleSubNav(subNavId, element) {
            const subNav = document.getElementById(subNavId);
            const icon = element.querySelector('i');

            subNav.classList.toggle('hidden');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        }
    </script>



    <div class="container mx-auto p-6">
        <!-- Dashboard Header -->
        <div class="flex justify-between mb-6 items-center">
            <h1 class="ml-64 text-3xl font-bold text-gray-800">Admin Dashboard</h1>
        </div>

      <!-- Dashboard Cards -->
      <div class="ml-64 grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Income Card -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-3xl transition duration-300 flex items-center">
                <div class="flex-shrink-0 bg-indigo-600 text-white p-4 rounded-full">
                    <i class="fas fa-dollar-sign text-3xl"></i>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold text-gray-800">Income</h2>
                    <p class="text-xl text-gray-600"><?= number_format($total_income, 2) ?></p>
                </div>
            </div>

            <!-- Total Orders Card -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-3xl transition duration-300 flex items-center">
                <div class="flex-shrink-0 bg-green-600 text-white p-4 rounded-full">
                    <i class="fas fa-box text-3xl"></i>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold text-gray-800">Orders</h2>
                    <p class="text-xl text-gray-600"><?= number_format($total_orders) ?></p>
                </div>
            </div>

            <!-- Total Quantity Sold Card -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-3xl transition duration-300 flex items-center">
                <div class="flex-shrink-0 bg-yellow-600 text-white p-4 rounded-full">
                    <i class="fas fa-cogs text-3xl"></i>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold text-gray-800">Quantity Sold</h2>
                    <p class="text-xl text-gray-600"><?= number_format($total_qty_sold) ?></p>
                </div>
            </div>
        </div>

<!-- Charts Section -->
<div class="ml-64 grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Order Status Pie Chart -->
    <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition duration-300">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Order Status Distribution</h3>
        <canvas id="orderStatusChart" style="width: 60%; height: 200px;"></canvas>
    </div>

    <!-- Total Income Bar Chart -->
    <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition duration-300">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Income by Order</h3>
        <canvas id="incomeChart" style="width: 60%; height: 200px;"></canvas>
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