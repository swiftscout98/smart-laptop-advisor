<?php
$conn = new mysqli('localhost', 'root', '', 'laptop_advisor_db');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$result = $conn->query("SELECT DISTINCT primary_use_case FROM products");
while($row = $result->fetch_assoc()) {
    echo $row['primary_use_case'] . "\n";
}
?>
