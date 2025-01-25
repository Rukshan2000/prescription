<?php
// Database connection
$mysqli = new mysqli("localhost", "root", "", "pharmacy");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Search filter logic
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Pagination logic
$limit = 5; // Number of entries per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch medicine with pagination and search filter
$sql = "SELECT * FROM medicine WHERE name LIKE '%$search_query%' LIMIT $limit OFFSET $offset";
$result = $mysqli->query($sql);

$medicine = [];
if ($result->num_rows > 0) {
    $medicine = $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch total number of records for pagination with search filter
$total_result = $mysqli->query("SELECT COUNT(*) AS total FROM medicine WHERE name LIKE '%$search_query%'");
$total_medicine = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_medicine / $limit);

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-4">Medicine Management</h1>

        <!-- Search bar -->
        <div class="mb-6">
            <form method="GET" action="">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search for medicine..." 
                    value="<?= htmlspecialchars($search_query) ?>" 
                    class="p-2 w-full md:w-1/3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                <button 
                    type="submit" 
                    class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-md shadow-md hover:bg-blue-700"
                >
                    Search
                </button>
            </form>
        </div>

        <!-- Medicine List as Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <?php if (!empty($medicine)): ?>
                <?php foreach ($medicine as $med): ?>
                    <div class="bg-white rounded-lg shadow-md p-4">
                        <img src="<?= $med['image'] ?>" alt="<?= $med['name'] ?>" class="w-full h-48 object-cover rounded-t-md">
                        <h3 class="text-lg font-bold mt-2"><?= $med['name'] ?></h3>
                        <p class="text-gray-600">Available Qty: <?= $med['available_qty'] ?></p>
                        <p class="text-gray-800 font-semibold mt-1">Price: LKR: <?= number_format($med['price'], 2) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center col-span-full text-gray-500">No medicines found.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination controls -->
        <div class="mt-6 flex justify-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a 
                    href="?search=<?= urlencode($search_query) ?>&page=<?= $i ?>" 
                    class="px-3 py-2 mx-1 bg-blue-500 text-white rounded-md shadow-md hover:bg-blue-700 <?= $i == $page ? 'bg-blue-700' : '' ?>"
                >
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>
