<?php
$conn = new mysqli('localhost', 'root', '', 'laptop_advisor_db');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$updates = [
    "UPDATE products SET primary_use_case = 'Professional' WHERE primary_use_case = 'Business'",
    "UPDATE products SET primary_use_case = 'Gamer' WHERE primary_use_case = 'Gaming'",
    "UPDATE products SET primary_use_case = 'Home User' WHERE primary_use_case IN ('General', 'General Use')",
    
    "UPDATE users SET primary_use_case = 'Professional' WHERE primary_use_case = 'Business'",
    "UPDATE users SET primary_use_case = 'Gamer' WHERE primary_use_case = 'Gaming'",
    "UPDATE users SET primary_use_case = 'Home User' WHERE primary_use_case IN ('General', 'General Use')"
];

foreach ($updates as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully: " . $sql . "\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}
$conn->close();
?>
