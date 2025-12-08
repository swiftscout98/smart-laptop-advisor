<?php
require_once 'admin/includes/db_connect.php';

echo "Verifying Data...\n";

// Count Users
$res = $conn->query("SELECT COUNT(*) as c FROM users");
$row = $res->fetch_assoc();
echo "Total Users: " . $row['c'] . "\n";

// Count Orders 2024
$res = $conn->query("SELECT COUNT(*) as c FROM orders WHERE YEAR(order_date) = 2024");
$row = $res->fetch_assoc();
echo "Orders in 2024: " . $row['c'] . "\n";

// Count Orders 2025
$res = $conn->query("SELECT COUNT(*) as c FROM orders WHERE YEAR(order_date) = 2025");
$row = $res->fetch_assoc();
echo "Orders in 2025: " . $row['c'] . "\n";

// Check for 1970 dates
$res = $conn->query("SELECT YEAR(order_date) as y, COUNT(*) as c FROM orders GROUP BY YEAR(order_date)");
while($row = $res->fetch_assoc()) {
    echo "Year " . $row['y'] . ": " . $row['c'] . " orders\n";
}

// Check Order Items
$res = $conn->query("SELECT COUNT(*) as c FROM order_items");
$row = $res->fetch_assoc();
echo "Total Order Items: " . $row['c'] . "\n";

echo "Done.\n";
?>
