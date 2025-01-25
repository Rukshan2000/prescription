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
                        echo "<script>setTimeout(function(){ window.location.href = 'home.html'; }, 2000);</script>";
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
            } else {
                $notification = "Error uploading the rejected prescription file.";
                $notification_class = "bg-red-500 text-white";
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
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <?php if (!empty($notification)): ?>
    <div class="fixed top-0 left-0 w-full p-4 text-center <?php echo $notification_class; ?>">
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
