<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'pharmacy');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT o.order_id, o.name AS user_name, o.email, o.doctor_mbbs_number, o.order_status, o.prescription_file
        FROM orders o
        ORDER BY o.order_id DESC";
$result = $conn->query($sql);

// Function to generate CSV and ZIP it with prescriptions
if (isset($_POST['download_zip'])) {
    // Create a CSV file in memory
    $csv_data = [];
    $csv_data[] = ['Order ID', 'User Name', 'Email', 'Doctor MBBS Number', 'Order Status', 'Prescription File'];

    while ($row = $result->fetch_assoc()) {
        $csv_data[] = [
            $row['order_id'], 
            $row['user_name'], 
            $row['email'], 
            $row['doctor_mbbs_number'], 
            $row['order_status'], 
            $row['prescription_file']
        ];

        // Download prescription files if they exist (assuming they're stored as paths)
        if ($row['prescription_file'] && file_exists($row['prescription_file'])) {
            $prescription_folder = 'prescriptions/';
            if (!is_dir($prescription_folder)) {
                mkdir($prescription_folder, 0777, true); // Create folder if not exists
            }
            copy($row['prescription_file'], $prescription_folder . basename($row['prescription_file'])); // Copy file to folder
        }
    }

    // Create the CSV file
    $csv_filename = 'order_report.csv';
    $csv_file = fopen('php://memory', 'w');
    foreach ($csv_data as $fields) {
        fputcsv($csv_file, $fields);
    }
    fseek($csv_file, 0);
    $csv_content = stream_get_contents($csv_file);
    fclose($csv_file);

    // Create a zip file and add CSV + prescriptions
    $zip = new ZipArchive();
    $zip_filename = 'order_report.zip';

    if ($zip->open($zip_filename, ZipArchive::CREATE) === TRUE) {
        // Add CSV to zip
        $zip->addFromString($csv_filename, $csv_content);

        // Add prescription folder to zip
        $files = glob($prescription_folder . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $zip->addFile($file, 'prescriptions/' . basename($file));
            }
        }

        // Close the zip
        $zip->close();

        // Force the download of the ZIP file
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Content-Length: ' . filesize($zip_filename));
        readfile($zip_filename);
        unlink($zip_filename); // Delete the temporary ZIP file after download
        exit();
    } else {
        echo "Failed to create ZIP file.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Report</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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

    <div class="container mx-auto my-6">
        <h1 class=" ml-64 text-3xl font-bold mb-4">Order Report</h1>
        
        <!-- Button to download as ZIP with CSV and prescriptions -->
        <form class="ml-64 mt-4" method="POST">
            <button type="submit" name="download_zip" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">Download ZIP (CSV + Prescriptions)</button>
        </form>

        <table class="ml-64 table-auto  border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border border-gray-300 px-4 py-2">Order ID</th>
                    <th class="border border-gray-300 px-4 py-2">User Name</th>
                    <th class="border border-gray-300 px-4 py-2">Email</th>
                    <th class="border border-gray-300 px-4 py-2">Doctor MBBS Number</th>
                    <th class="border border-gray-300 px-4 py-2">Order Status</th>
                    <th class="border border-gray-300 px-4 py-2">Prescription</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td class="border border-gray-300 px-4 py-2"><?= $row['order_id'] ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?= $row['user_name'] ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?= $row['email'] ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?= $row['doctor_mbbs_number'] ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?= $row['order_status'] ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?= $row['prescription_file'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php $conn->close(); ?>
