<?php
$mysqli = new mysqli("localhost", "root", "", "pharmacy");

// Fetch medicine names from the database
$result = $mysqli->query("SELECT name FROM medicine");
$medicines = [];

while ($row = $result->fetch_assoc()) {
    $medicines[] = $row;
}

// Return medicines as JSON
echo json_encode($medicines);
?>
