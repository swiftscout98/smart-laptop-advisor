<?php
require_once '../LaptopAdvisor/includes/db_connect.php';

$sql = "SELECT intent_name, usage_count FROM intents WHERE intent_id = 3";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
echo "Intent: " . $row['intent_name'] . " - Usage Count: " . $row['usage_count'] . "\n";
?>
