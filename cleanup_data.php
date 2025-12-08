<?php
require_once 'admin/includes/db_connect.php';

echo "Cleaning up invalid orders...\n";
// 1. Get invalid order IDs
$result = $conn->query("SELECT order_id FROM orders WHERE YEAR(order_date) = 0");
$ids = [];
while ($row = $result->fetch_assoc()) {
    $ids[] = $row['order_id'];
}

if (!empty($ids)) {
    $id_list = implode(',', $ids);
    // 2. Delete items
    $conn->query("DELETE FROM order_items WHERE order_id IN ($id_list)");
    echo "Deleted items for " . count($ids) . " orders.\n";
    
    // 3. Delete orders
    $conn->query("DELETE FROM orders WHERE order_id IN ($id_list)");
    echo "Deleted " . count($ids) . " orders.\n";
} else {
    echo "No invalid orders found.\n";
}
echo "Done.\n";
?>
