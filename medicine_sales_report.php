<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'pharmacy');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the total income and quantity sold for each medicine
$sql = "SELECT m.name AS medicine_name, 
               SUM(od.quantity) AS total_qty_sold, 
               SUM(od.quantity * m.price) AS total_income
        FROM medicine m
        JOIN order_details od ON m.name = od.medicine_name
        GROUP BY m.name";
$result = $conn->query($sql);

// Query for total income from all medicines
$sql_total_income = "SELECT SUM(od.quantity * m.price) AS total_income
                     FROM medicine m
                     JOIN order_details od ON m.name = od.medicine_name";
$result_total_income = $conn->query($sql_total_income);
$total_income = $result_total_income->fetch_assoc()['total_income'];

// Function to output data as CSV
if (isset($_POST['download_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="medicine_sales_report.csv"');
    $output = fopen('php://output', 'w');
    
    // Add CSV column headers
    fputcsv($output, ['Medicine Name', 'Total Quantity Sold', 'Income']);
    
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
    <title>Medicine Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mx-auto my-6">
        <h1 class="text-3xl font-bold mb-4">Medicine Sales Report</h1>
        <h2 class="text-xl mb-4">Total Income: <?= number_format($total_income, 2) ?> </h2>
        
        <!-- Button to download as CSV -->
        <form method="POST">
            <button type="submit" name="download_csv" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">Download as CSV</button>
        </form>

        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border border-gray-300 px-4 py-2">Medicine Name</th>
                    <th class="border border-gray-300 px-4 py-2">Total Quantity Sold</th>
                    <th class="border border-gray-300 px-4 py-2">Income</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td class="border border-gray-300 px-4 py-2"><?= $row['medicine_name'] ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?= $row['total_qty_sold'] ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?= number_format($row['total_income'], 2) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php $conn->close(); ?>
