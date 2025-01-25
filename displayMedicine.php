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
  <!-- Top Navbar -->
  <header class="bg-blue-500 text-white py-4 shadow-md">

<nav class="bg-white dark:bg-blue-500 fixed w-full z-20 top-0 start-0 border-b border-blue-200 dark:border-blue-600">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
        <a href="https://flowbite.com/" class="flex items-center space-x-3 rtl:space-x-reverse">
            <h1 class="text-3xl font-bold">Medi Care</h1>
        </a>
        <div class="flex md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
            <button type="button"
            onclick="window.location.href='order.php';"
            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center dark:bg-blue-800 dark:hover:bg-blue-900 dark:focus:ring-blue-800">
            Order
        </button>
        
            <button id="toggleButton" data-collapse-toggle="navbar-sticky" type="button"
                class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-blue-500 rounded-lg md:hidden hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:text-blue-400 dark:hover:bg-blue-700 dark:focus:ring-blue-600"
                aria-controls="navbar-sticky" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M1 1h15M1 7h15M1 13h15" />
                </svg>
            </button>
        </div>
        <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
            <ul class="flex flex-col p-4 md:p-0 mt-4 font-medium border border-blue-100 rounded-lg bg-blue-50 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-white dark:bg-blue-800 md:dark:bg-blue-500 dark:border-blue-700">
                <li>
                    <a href="index.html#home" class="block py-2 px-3 text-blue-500 rounded hover:bg-blue-100 md:hover:bg-transparent md:hover:underline md:p-0 dark:text-white dark:hover:bg-transparent dark:hover:underline">Home</a>
                </li>
                <li>
                    <a href="index.html#about" class="block py-2 px-3 text-blue-500 rounded hover:bg-blue-100 md:hover:bg-transparent md:hover:underline md:p-0 dark:text-white dark:hover:bg-transparent dark:hover:underline">About</a>
                </li>
                <li>
                    <a href="index.htmldisplayMedicine.php" class="block py-2 px-3 text-blue-500 rounded hover:bg-blue-100 md:hover:bg-transparent md:hover:underline md:p-0 dark:text-white dark:hover:bg-transparent dark:hover:underline">Products</a>
                </li>
                <li>
                    <a href="index.html#services" class="block py-2 px-3 text-blue-500 rounded hover:bg-blue-100 md:hover:bg-transparent md:hover:underline md:p-0 dark:text-white dark:hover:bg-transparent dark:hover:underline">Services</a>
                </li>
                <li>
                    <a href="index.html#contact" class="block py-2 px-3 text-blue-500 rounded hover:bg-blue-100 md:hover:bg-transparent md:hover:underline md:p-0 dark:text-white dark:hover:bg-transparent dark:hover:underline">Contact</a>
                </li>
            </ul>
        </div>
        
    </div>
</nav>

</header>
<body class="bg-gray-100 text-gray-800">
    <div class="container mx-auto p-6">
        <h1 class="mt-10 text-3xl font-bold mb-4">Medi Care</h1>

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
