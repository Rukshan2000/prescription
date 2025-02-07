<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'pharmacy');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT name, available_qty FROM medicine";
$result = $conn->query($sql);

// Function to output data as CSV
if (isset($_POST['download_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="inventory_report.csv"');
    $output = fopen('php://output', 'w');
    
    // Add CSV column headers
    fputcsv($output, ['Medicine Name', 'Available Quantity']);
    
    // Output data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>

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

    <div class="container mx-auto my-6">
        <h1 class="ml-64 text-3xl font-bold mb-4">Inventory Report</h1>
        
        <!-- Button to download as CSV -->
        <form class="ml-64" method="POST">
            <button type="submit" name="download_csv" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">Download as CSV</button>
        </form>

        <table class="ml-64 table-auto w-3/4 border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border border-gray-300 px-4 py-2">Medicine Name</th>
                    <th class="border border-gray-300 px-4 py-2">Available Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td class="border border-gray-300 px-4 py-2"><?= $row['name'] ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?= $row['available_qty'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php $conn->close(); ?>
