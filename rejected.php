<?php
$mysqli = new mysqli("localhost", "root", "", "pharmacy");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch rejected orders
$result = $mysqli->query("SELECT * FROM rejected_orders");

$zipFilePath = 'exported_data.zip';
$csvFilePath = 'rejected_orders.csv';

// CSV Export and ZIP Creation
if (isset($_POST['export_csv'])) {
    // Create a ZIP file that will contain both images and CSV file
    $zip = new ZipArchive();
    if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
        // Add images to the zip file
        while ($row = $result->fetch_assoc()) {
            $prescriptionFile = $row['prescription_file'];
            if (file_exists($prescriptionFile)) {
                $zip->addFile($prescriptionFile, basename($prescriptionFile)); // Add prescription files to the ZIP
            }
        }

        // Create CSV file and add it to the ZIP
        $csvOutput = fopen($csvFilePath, 'w');
        fputcsv($csvOutput, ['Name', 'Email', 'Doctor MBBS Number', 'Rejected At', 'Prescription File']);

        // Move the result pointer back to the start for CSV export
        $result->data_seek(0); // Reset the pointer for the CSV export
        while ($row = $result->fetch_assoc()) {
            fputcsv($csvOutput, [
                $row['name'],
                $row['email'],
                $row['doctor_mbbs_number'],
                $row['rejected_at'],
                $row['prescription_file']
            ]);
        }
        fclose($csvOutput);

        // Add the CSV file to the ZIP
        $zip->addFile($csvFilePath, 'rejected_orders.csv');

        // Close the ZIP file
        $zip->close();
    }

    // Force the download of the ZIP file containing both images and CSV
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="exported_data.zip"');
    header('Content-Length: ' . filesize($zipFilePath));

    // Output the ZIP file to the browser
    readfile($zipFilePath);

    // Delete the temporary files after sending them to the user
    unlink($zipFilePath);
    unlink($csvFilePath); // Remove the CSV file after adding it to the ZIP

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>

<body class="bg-gray-100 min-h-screen flex">

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
                <li><a href="medicine_sales_report.php" class="block py-2 px-4 hover:bg-gray-600 rounded-md">Medicine sales Report</a></li>
                <li><a href="order_report.php" class="block py-2 px-4 hover:bg-gray-600 rounded-md">Order Report</a></li>
                <li><a href="inventory_report.php" class="block py-2 px-4 hover:bg-gray-600 rounded-md">Inventory Report</a></li>
            </ul>
        </li>
    </ul>

    <!-- Logout Button -->
    <div class="mt-48">
        <a href="admin_login.php">
            <button class="w-full bg-red-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-400">
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


    <!-- Main Content -->
    <div class="ml-64 flex-1 p-8">
        <h1 class="text-3xl font-semibold text-center text-gray-800 mb-6">Rejected Orders</h1>

        <!-- Export CSV and Images as ZIP Button -->
        <form method="POST" class="text-center mb-6">
            <button type="submit" name="export_csv"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Export as CSV and
                prescriptions</button>
        </form>

        <?php if ($result->num_rows > 0): ?>
            <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Name</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Email</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Doctor MBBS Number</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Rejected At</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Prescription File</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="py-4 px-6 text-sm text-gray-800"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="py-4 px-6 text-sm text-gray-800"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="py-4 px-6 text-sm text-gray-800">
                                <?php echo htmlspecialchars($row['doctor_mbbs_number']); ?></td>
                            <td class="py-4 px-6 text-sm text-gray-800"><?php echo htmlspecialchars($row['rejected_at']); ?>
                            </td>
                            <td class="py-4 px-6 text-sm text-gray-800">
                                <!-- Check if file exists, then show the link -->
                                <?php if (file_exists($row['prescription_file'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['prescription_file']); ?>" target="_blank"
                                        class="text-blue-600 hover:text-blue-800">View Prescription</a>
                                <?php else: ?>
                                    <span class="text-red-600">File not found</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-gray-600">No rejected orders found.</p>
        <?php endif; ?>

    </div>

</body>

</html>

<?php
// Close connection
$mysqli->close();
?>