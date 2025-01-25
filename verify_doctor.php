<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pharmacy";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$doctor_data = null;

// Check if the form is submitted and process the input
if (isset($_POST['mbbs_number'])) {
    $mbbs_number = $_POST['mbbs_number'];

    // Prepare the SQL query to search for the doctor
    $sql = "SELECT * FROM doctors WHERE mbbs_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mbbs_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $doctor_data = $result->fetch_assoc();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body class="bg-gray-100 font-sans">
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


    <div class="ml-64 flex justify-center items-center min-h-screen bg-gray-100 py-8 px-4">
        <div class="max-w-lg w-full bg-white shadow-lg rounded-lg p-6 space-y-6">
            <h1 class="text-3xl font-semibold text-center text-gray-800">Doctor Verification</h1>

            <!-- Form Section -->
            <form action="verify_doctor.php" method="POST" class="space-y-4">
                <div>
                    <label for="mbbs_number" class="block text-lg font-medium text-gray-700">Enter MBBS Number</label>
                    <input type="text" id="mbbs_number" name="mbbs_number" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-500 transition duration-300">Verify Doctor</button>
            </form>

            <!-- Display Doctor Data or Not Found Message -->
            <?php if ($doctor_data): ?>
                <div class="mt-8 p-6 bg-green-50 rounded-md">
                    <h2 class="text-2xl font-semibold text-green-800">Doctor Found</h2>
                    <div class="mt-4 flex items-center space-x-4">
                    <img src="doctors/<?= $doctor_data['doctor_image'] ?>" alt="<?= $doctor_data['name'] ?>'s Image" class="w-24 h-24 rounded-full shadow-lg">
                    <div class="text-gray-800">
                            <p><strong>Name:</strong> <?php echo $doctor_data['name']; ?></p>
                            <p><strong>MBBS Number:</strong> <?php echo $doctor_data['mbbs_number']; ?></p>
                            <p><strong>Specialization:</strong> <?php echo $doctor_data['specialization']; ?></p>
                            <p><strong>Phone:</strong> <?php echo $doctor_data['phone_number']; ?></p>
                            <p><strong>Email:</strong> <?php echo $doctor_data['email']; ?></p>
                            <p><strong>Address:</strong> <?php echo $doctor_data['address']; ?></p>
                            
                        </div>
                    </div>
                </div>
            <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                <div class="mt-8 p-6 bg-red-50 rounded-md">
                    <p class="text-red-600 font-medium">Doctor not registered. Please check the MBBS number and try again.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
