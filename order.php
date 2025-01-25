<?php
$mysqli = new mysqli("localhost", "root", "", "pharmacy");

$notification = '';
$notification_class = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $doctor_mbbs_number = $_POST['doctor_mbbs_number'];

    // Check if the doctor's MBBS number is registered
    $doctor_check = $mysqli->query("SELECT * FROM doctors WHERE mbbs_number = '$doctor_mbbs_number'");

    if ($doctor_check->num_rows > 0) {
        // Check if the user exists in the users table and get the user_id
        $user_check = $mysqli->query("SELECT user_id FROM users WHERE email = '$email'");
        
        if ($user_check->num_rows > 0) {
            // Get the user_id
            $user = $user_check->fetch_assoc();
            $user_id = $user['user_id'];  // Use user_id here

            // Check if a file was uploaded
            if (isset($_FILES['prescription_file']) && $_FILES['prescription_file']['error'] == 0) {
                $file_tmp_name = $_FILES['prescription_file']['tmp_name'];
                $file_name = $_FILES['prescription_file']['name'];
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                
                // Validate the file type (only allowed extensions)
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
                if (in_array($file_extension, $allowed_extensions)) {
                    // Create the folder if it doesn't exist
                    $upload_dir = 'prescriptions/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    // Generate a unique file name to avoid overwriting
                    $new_file_name = uniqid() . '.' . $file_extension;

                    // Move the uploaded file to the prescriptions folder
                    if (move_uploaded_file($file_tmp_name, $upload_dir . $new_file_name)) {
                        $prescription_file_path = $upload_dir . $new_file_name;

                        // Insert order into orders table with user_id, name, email, and prescription file path
                        $mysqli->query("INSERT INTO orders (user_id, name, email, doctor_mbbs_number, prescription_file) 
                                        VALUES ('$user_id', '$name', '$email', '$doctor_mbbs_number', '$prescription_file_path')");

                        $notification = "Order placed successfully!";
                        $notification_class = "bg-green-500 text-white";
                        echo "<script>setTimeout(function(){ window.location.href = 'index.html'; }, 2000);</script>";
                    } else {
                        $notification = "Error uploading the prescription file.";
                        $notification_class = "bg-red-500 text-white";
                    }
                } else {
                    $notification = "Only JPG, PNG, PDF, DOC, DOCX files are allowed.";
                    $notification_class = "bg-red-500 text-white";
                }
            } else {
                $notification = "Please upload a prescription file.";
                $notification_class = "bg-red-500 text-white";
            }
        } else {
            $notification = "No user found with the given email.";
            $notification_class = "bg-red-500 text-white";
        }
    } else {
        // If the doctor is not registered, save the file to the rejected folder
        if (isset($_FILES['prescription_file']) && $_FILES['prescription_file']['error'] == 0) {
            $file_tmp_name = $_FILES['prescription_file']['tmp_name'];
            $file_name = $_FILES['prescription_file']['name'];
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

            // Create the rejected folder if it doesn't exist
            $rejected_dir = 'rejected/';
            if (!file_exists($rejected_dir)) {
                mkdir($rejected_dir, 0777, true);
            }

            // Generate a unique file name for rejected files, keeping the original file extension
            $new_file_name_rejected = uniqid() . '.' . $file_extension;

            // Move the uploaded file to the rejected folder
            if (move_uploaded_file($file_tmp_name, $rejected_dir . $new_file_name_rejected)) {
                $rejected_file_path = $rejected_dir . $new_file_name_rejected;

                // Insert rejected order into rejected_orders table
                $mysqli->query("INSERT INTO rejected_orders (name, email, doctor_mbbs_number, rejected_at, prescription_file) 
                                VALUES ('$name', '$email', '$doctor_mbbs_number', NOW(), '$rejected_file_path')");

                $notification = "The doctor is not registered. Order rejected and prescription file saved.";
                $notification_class = "bg-red-500 text-white";
                echo "<script>setTimeout(function(){ window.location.href = 'index.html'; }, 2000);</script>";

            } else {
                $notification = "Error uploading the rejected prescription file.";
                $notification_class = "bg-red-500 text-white";
                echo "<script>setTimeout(function(){ window.location.href = 'index.html'; }, 2000);</script>";

            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Order</title>
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
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <?php if (!empty($notification)): ?>
    <div class="fixed bottom-0 left-0 w-full p-4 text-center <?php echo $notification_class; ?>">
        <?php echo $notification; ?>
    </div>
    <?php endif; ?>

    <div class="bg-white shadow-md rounded-lg p-8 max-w-lg w-full">
        <h1 class="text-2xl font-semibold text-center text-gray-800 mb-6">Place Your Prescription Order</h1>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="name" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>

            <div>
                <label for="doctor_mbbs_number" class="block text-sm font-medium text-gray-700">Doctor MBBS Number</label>
                <input type="text" name="doctor_mbbs_number" id="doctor_mbbs_number" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>

            <div>
                <label for="prescription_file" class="block text-sm font-medium text-gray-700">Upload Prescription</label>
                <input type="file" name="prescription_file" id="prescription_file" class="mt-1 block w-full text-gray-700 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>

            <div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-full transition ease-in-out duration-150">Place Order</button>
            </div>
        </form>
    </div>
</body>
</html>
