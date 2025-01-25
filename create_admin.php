<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'pharmacy');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the admin user
    $sql = "INSERT INTO admin_users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $username, $hashed_password);
    $stmt->execute();

    // Close the connection
    $conn->close();

    // Redirect to success page
    header('Location: create_admin.php?status=success');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Side Navigation -->
    <div class="w-64 bg-gray-800 text-white h-screen px-4 py-8 flex flex-col">
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
                <li><a href="medicine_sales_report.php" class="block py-2 px-4 hover:bg-gray-600 rounded-md">Medicine sales Report</a></li>
                <li><a href="order_report.php" class="block py-2 px-4 hover:bg-gray-600 rounded-md">Order Report</a></li>
                <li><a href="inventory_report.php" class="block py-2 px-4 hover:bg-gray-600 rounded-md">Inventory Report</a></li>
            </ul>
        </li>
    </ul>

    <!-- Logout Button -->
    <div class="mt-auto">
        <a href="admin_login.php">
            <button class="w-full bg-red-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-400">
                Logout
            </button>
        </a>
    </div>
</div>
    <div class="ml container mx-auto p-6">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6 text-center">Create Admin User</h2>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="text-green-500 text-center mb-4">
                    <p>Admin user created successfully!</p>
                    <a href="admin_login.php" class="bg-blue-500 text-white px-4 py-2 rounded-md mt-4 inline-block">Go to Admin Login</a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="username" name="username" required class="mt-2 p-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password" required class="mt-2 p-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md w-full">Create Admin</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
