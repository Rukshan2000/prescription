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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mx-auto my-6">
        <h1 class="text-3xl font-bold mb-4">Inventory Report</h1>
        
        <!-- Button to download as CSV -->
        <form method="POST">
            <button type="submit" name="download_csv" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">Download as CSV</button>
        </form>

        <table class="table-auto w-full border-collapse border border-gray-300">
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
