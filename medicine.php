<?php
// Database connection
$mysqli = new mysqli("localhost", "root", "", "pharmacy");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$notification = ""; // Variable for the green notification

// Insert medicine and handle image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $available_qty = $_POST['available_qty'];
    $price = $_POST['price'];  // Capture price

    // Handle image upload
    $target_dir = "medicine/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is an image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        die("File is not an image.");
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        die("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }

    // Move uploaded file to target directory
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Insert into database
        $stmt = $mysqli->prepare("INSERT INTO medicine (name, available_qty, image, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sisd", $name, $available_qty, $target_file, $price);  // Bind price

        if ($stmt->execute()) {
            // Success message
            $notification = "New medicine added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

// Medicine update functionality
if (isset($_GET['edit'])) {
    $med_id = $_GET['edit'];
    $med_result = $mysqli->query("SELECT * FROM medicine WHERE medicine_id = $med_id");
    $medicine_to_update = $med_result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
        $name = $_POST['name'];
        $available_qty = $_POST['available_qty'];
        $price = $_POST['price'];
        $image = $_FILES['image']['name'] ? $_FILES['image']['name'] : $medicine_to_update['image'];

        // Image upload if new image is provided
        if ($_FILES['image']['name']) {
            $target_dir = "medicine/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        }

        $update_stmt = $mysqli->prepare("UPDATE medicine SET name = ?, available_qty = ?, price = ?, image = ? WHERE medicine_id = ?");
        $update_stmt->bind_param("sisd", $name, $available_qty, $price, $image, $med_id);

        if ($update_stmt->execute()) {
            $notification = "Medicine updated successfully!";
            header("Location: index.php");
        } else {
            echo "Error: " . $update_stmt->error;
        }

        $update_stmt->close();
    }
}

// Pagination logic
$limit = 5; // Number of entries per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search logic
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Filter logic
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$query = "SELECT * FROM medicine WHERE name LIKE ?"; // Modified to use search

if ($filter == 'low_stock') {
    $query .= " AND available_qty < 100";
}

$query .= " LIMIT $limit OFFSET $offset";
$stmt = $mysqli->prepare($query);
$search_param = "%" . $search . "%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();

$medicine = [];
if ($result->num_rows > 0) {
    $medicine = $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch total number of records for pagination
$total_query = "SELECT COUNT(*) AS total FROM medicine WHERE name LIKE ?";
if ($filter == 'low_stock') {
    $total_query .= " AND available_qty < 100";
}
$total_stmt = $mysqli->prepare($total_query);
$total_stmt->bind_param("s", $search_param);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
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

    <div class="container mx-auto p-6">
        <h1 class="ml-64 text-3xl font-bold mb-4">Medicine Management</h1>

        <!-- Green notification for medicine added successfully -->
        <?php if (!empty($notification)): ?>
            <div class="bg-green-500 text-white p-4 rounded-lg mb-4">
                <?= $notification ?>
            </div>
        <?php endif; ?>

        <!-- Form to add new medicine -->
        <div class="ml-64 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4">Add New Medicine</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Medicine Name</label>
                    <input type="text" id="name" name="name" required class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-4">
                    <label for="available_qty" class="block text-sm font-medium text-gray-700">Available Quantity</label>
                    <input type="number" id="available_qty" name="available_qty" required class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-4">
                    <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                    <input type="number" id="price" name="price" required step="0.01" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-4">
                    <label for="image" class="block text-sm font-medium text-gray-700">Upload Image</label>
                    <input type="file" id="image" name="image" accept="image/*" required class="mt-1 block w-full">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-md hover:bg-blue-700">Add Medicine</button>
            </form>
        </div>

        <hr class="my-6">

        <!-- Search and filter form -->
        <div class="ml-64 flex justify-between mb-4">
            <form action="" method="GET" class="w-1/2">
                <input type="text" name="search" placeholder="Search medicine..." value="<?= $search ?>" class="w-full p-2 border border-gray-300 rounded-md shadow-sm">
            </form>
            <form id="filterForm" method="GET" class="w-1/4">
                <select name="filter" id="filter" class="p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" onchange="this.form.submit()">
                    <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>All Medicines</option>
                    <option value="low_stock" <?= $filter == 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
                </select>
            </form>
        </div>

        <div class="ml-64 bg-white p-6 rounded-lg shadow-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Medicine ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Available Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Image</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($medicine)): ?>
                        <?php foreach ($medicine as $med): ?>
                            <tr class="<?= $med['available_qty'] < 100 ? 'bg-red-100' : '' ?>">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= $med['medicine_id'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= $med['name'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= $med['available_qty'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= $med['price'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><img src="<?= $med['image'] ?>" alt="<?= $med['name'] ?>" class="w-12 h-12 object-cover rounded-md"></td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <a href="?edit=<?= $med['medicine_id'] ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-6 py-4 text-sm text-center">No medicines found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4">
                <ul class="flex justify-center space-x-2">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li>
                            <a href="?page=<?= $i ?>&search=<?= $search ?>&filter=<?= $filter ?>" class="px-4 py-2 <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200' ?> rounded-md"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>