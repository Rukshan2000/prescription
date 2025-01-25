<?php
$mysqli = new mysqli("localhost", "root", "", "medicine_system");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $mbbs_number = $_POST['mbbs_number'];

    $mysqli->query("INSERT INTO doctors (name, mbbs_number) VALUES ('$name', '$mbbs_number')");
    echo "Doctor registered successfully!";
}
?>

<form method="POST">
    Doctor Name: <input type="text" name="name" required><br>
    MBBS Number: <input type="text" name="mbbs_number" required><br>
    <input type="submit" value="Register Doctor">
</form>
