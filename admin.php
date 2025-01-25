<?php 
$mysqli = new mysqli("localhost", "root", "", "pharmacy");

// Handle updating order status
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];

    if (isset($_POST['medicine_name']) && is_array($_POST['medicine_name']) && isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        $medicine_names = $_POST['medicine_name'];
        $quantities = $_POST['quantity'];
        $action = $_POST['action'];

        // Check if doctor is registered
        $order = $mysqli->query("SELECT * FROM orders WHERE order_id = $order_id")->fetch_assoc();
        $doctor_mbbs_number = $order['doctor_mbbs_number'];
        $doctor = $mysqli->query("SELECT * FROM doctors WHERE mbbs_number = '$doctor_mbbs_number'")->fetch_assoc();

        if (!$doctor) {
            // Cancel the order
            $mysqli->query("UPDATE orders SET order_status = 'canceled' WHERE order_id = $order_id");
            $email_body = "Your order has been canceled as the doctor is not registered.";
            $mysqli->query("INSERT INTO emails (order_id, email_subject, email_body) VALUES ($order_id, 'Order Canceled', '$email_body')");
            echo "<p class='text-red-500'>Order canceled.</p><br>";
        } else {
            // Place the order and update multiple medicine details
            $mysqli->query("UPDATE orders SET order_status = 'ready' WHERE order_id = $order_id");

            $total_price = 0;
            $medicine_details = "";
            foreach ($medicine_names as $index => $medicine_name) {
                $quantity = $quantities[$index];
                $medicine = $mysqli->query("SELECT * FROM medicine WHERE name = '$medicine_name'")->fetch_assoc();

                if ($medicine && $medicine['available_qty'] >= $quantity) {
                    $new_qty = $medicine['available_qty'] - $quantity;
                    $price_per_unit = $medicine['price']; // Assuming the 'price' column exists in the 'medicine' table
                    $total_price += $price_per_unit * $quantity;

                    $mysqli->query("UPDATE medicine SET available_qty = $new_qty WHERE name = '$medicine_name'");
                    $mysqli->query("INSERT INTO order_details (order_id, medicine_name, quantity) VALUES ($order_id, '$medicine_name', $quantity)");
                    $medicine_details .= "Medicine: $medicine_name, Quantity: $quantity, Price: $price_per_unit each\n";
                } else {
                    echo "<p class='text-yellow-500'>Not enough stock for $medicine_name.</p><br>";
                }
            }

            // Send email with total price
            $email_body = "Your order is ready with the following medicines:\n" . $medicine_details . "\nTotal Price: " . $total_price;
            $mysqli->query("INSERT INTO emails (order_id, email_subject, email_body) VALUES ($order_id, 'Order Ready', '$email_body')");
            echo "<p class='text-green-500'>Order processed and ready. Total Price: $total_price</p><br>";

            // Fetch user's email from the users table using prepared statement
            $user_email_query = $mysqli->prepare("SELECT email FROM users WHERE user_id = ?");
            $user_email_query->bind_param("i", $order['user_id']); // "i" is for integer
            $user_email_query->execute();

            // Fetch the result
            $result = $user_email_query->get_result();
            $user_email = $result->fetch_assoc()['email']; 

            // Generate Gmail URL
            $gmail_url = "https://mail.google.com/mail/?view=cm&fs=1&to=$user_email&su=" . urlencode('Order Ready') . "&body=" . urlencode($email_body);

            // Show success message and provide Gmail link
            echo "<script>
                    alert('Email sent to $user_email');
                    window.location.href = '$gmail_url';
                  </script>";
        }
    } else {
        echo "<p class='text-red-500'>Please add at least one medicine and quantity.</p><br>";
    }
}

// Fetch medicine names from the database
$result = $mysqli->query("SELECT name FROM medicine");
$medicines = [];
while ($row = $result->fetch_assoc()) {
    $medicines[] = $row;
}

// Display orders in table format
$orders = $mysqli->query("SELECT * FROM orders WHERE order_status = 'pending'");
?>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

<div class="ml-64 container mx-auto p-8 bg-gray-50 min-h-screen">
    
    <h2 class="text-3xl font-semibold mb-6 text-gray-800">Pending Orders</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow rounded-lg">
            <thead>
                <tr class="bg-gray-200 text-gray-700">
                    <th class="py-4 px-6 text-left font-semibold">Order ID</th>
                    <th class="py-4 px-6 text-left font-semibold">Name</th>
                    <th class="py-4 px-6 text-left font-semibold">Email</th>
                    <th class="py-4 px-6 text-left font-semibold">Doctor MBBS Number</th>
                    <th class="py-4 px-6 text-left font-semibold">Order Status</th>
                    <th class="py-4 px-6 text-left font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-100">
                        <td class="py-4 px-6 border-b"><?= $order['order_id'] ?></td>
                        <td class="py-4 px-6 border-b"><?= $order['name'] ?></td>
                        <td class="py-4 px-6 border-b"><?= $order['email'] ?></td>
                        <td class="py-4 px-6 border-b"><?= $order['doctor_mbbs_number'] ?></td>
                        <td class="py-4 px-6 border-b"><?= $order['order_status'] ?></td>
                        <td class="py-4 px-6 border-b">
                            <button class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg" onclick="viewOrder(<?= $order['order_id'] ?>)">View</button>
                        </td>
                    </tr>
                    <tr id="details-<?= $order['order_id'] ?>" class="hidden">
                        <td colspan="6" class="py-6 px-8 bg-gray-50">
                            <form method="POST" class="space-y-6">
                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                <div>
                                    <p class="text-lg text-gray-800 font-semibold">Order ID: <?= $order['order_id'] ?></p>
                                    <p class="text-lg text-gray-800">Name: <?= $order['name'] ?></p>
                                    <p class="text-lg text-gray-800">Email: <?= $order['email'] ?></p>
                                    <p class="text-lg text-gray-800">Doctor MBBS Number: <?= $order['doctor_mbbs_number'] ?></p>
                                  
                                    <p class="mt-4">
    <span class="font-semibold text-gray-800">Prescription :</span>
    <a href="<?= $order['prescription_file'] ?>" target="_blank" class="text-blue-500 hover:underline">
        View Prescription
    </a>
</p>
                                </div>

                                <div id="medicine-fields-<?= $order['order_id'] ?>" class="space-y-4">
                                    <div class="flex items-center space-x-4">
                                        <label class="text-gray-700 font-semibold">Medicine Name:</label>
                                        <select name="medicine_name[]" required class="block w-full bg-gray-100 border border-gray-300 rounded-md p-2 focus:outline-none focus:ring focus:border-blue-500">
                                            <?php foreach ($medicines as $medicine): ?>
                                                <option value="<?= $medicine['name'] ?>"><?= $medicine['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label class="text-gray-700 font-semibold">Quantity:</label>
                                        <input type="number" name="quantity[]" required class="block w-20 bg-gray-100 border border-gray-300 rounded-md p-2 focus:outline-none focus:ring focus:border-blue-500">
                                    </div>
                                </div>

                                <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg mt-4" onclick="addMedicineField(<?= $order['order_id'] ?>)">Add More Medicine</button>

                                <input type="submit" name="action" value="Process Order" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded-lg mt-6">
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function viewOrder(orderId) {
        var detailsRow = document.getElementById('details-' + orderId);
        if (detailsRow.classList.contains('hidden')) {
            detailsRow.classList.remove('hidden');
        } else {
            detailsRow.classList.add('hidden');
        }
    }

    function addMedicineField(orderId) {
        var div = document.createElement('div');
        div.className = 'flex items-center space-x-4';
        div.innerHTML = 'Medicine Name: <select name="medicine_name[]" required class="block w-full bg-gray-100 border border-gray-300 rounded-md p-2 focus:outline-none focus:ring focus:border-blue-500">' +
            <?php foreach ($medicines as $medicine): ?>
                '<option value="<?= $medicine['name'] ?>"><?= $medicine['name'] ?></option>' +
            <?php endforeach; ?>
            '</select>' +
            'Quantity: <input type="number" name="quantity[]" required class="block w-20 bg-gray-100 border border-gray-300 rounded-md p-2 focus:outline-none focus:ring focus:border-blue-500">';
        document.getElementById('medicine-fields-' + orderId).appendChild(div);
    }
</script>
</body>
</html>
