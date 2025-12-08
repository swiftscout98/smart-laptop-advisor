<?php
require_once 'admin/includes/db_connect.php';

echo "Starting Dummy Data Generation...\n";

// --- 1. Generate Users ---
$users = [
    ['Frank Miller', 'frank.m@example.com', 'Professional'],
    ['Sarah Connor', 'sarah.c@sky.net', 'Gamer'],
    ['John Doe', 'john.doe@test.com', 'Student'],
    ['Jane Smith', 'jane.smith@test.com', 'Creative'],
    ['Alice Johnson', 'alice.j@test.com', 'Home User'],
    ['Bob Brown', 'bob.b@test.com', 'Professional'],
    ['Charlie Davis', 'charlie.d@test.com', 'Gamer'],
    ['Diana Evans', 'diana.e@test.com', 'Student'],
    ['Ethan Hunt', 'ethan.h@imf.org', 'Creative'],
    ['Fiona Gallagher', 'fiona.g@test.com', 'Home User']
];

$user_ids = [];
$password_hash = password_hash('password123', PASSWORD_DEFAULT);

echo "Creating Users...\n";
foreach ($users as $u) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $u[1]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_ids[] = $row['user_id'];
        echo "User {$u[0]} already exists (ID: {$row['user_id']}).\n";
    } else {
        $created_at = date('Y-m-d H:i:s', strtotime('-' . rand(1, 24) . ' months'));
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, status, primary_use_case, created_at) VALUES (?, ?, ?, 'active', ?, ?)");
        $stmt->bind_param("sssss", $u[0], $u[1], $password_hash, $u[2], $created_at);
        if ($stmt->execute()) {
            $user_ids[] = $stmt->insert_id;
            echo "Created user {$u[0]} (ID: " . $stmt->insert_id . ").\n";
        } else {
            echo "Failed to create user {$u[0]}: " . $stmt->error . "\n";
        }
    }
}

// Get existing users too if needed
if (count($user_ids) < 5) {
    $result = $conn->query("SELECT user_id FROM users LIMIT 50");
    while($row = $result->fetch_assoc()) {
        if (!in_array($row['user_id'], $user_ids)) {
            $user_ids[] = $row['user_id'];
        }
    }
}

// --- 2. Get Products ---
$products = [];
$result = $conn->query("SELECT product_id, price FROM products");
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
if (empty($products)) {
    die("Error: No products found in database. Please add products first.\n");
}

// --- 3. Generate Orders ---
echo "Generating Orders for 2024 and 2025...\n";

$start_date = strtotime('2024-01-01');
$end_date = strtotime('2025-12-31');
$current_date = $start_date;
$orders_created = 0;

while ($current_date <= $end_date) {
    // Generate 15-30 orders per month for decent density
    $orders_this_month = rand(15, 30);
    $month_str = date('Y-m', $current_date);
    
    for ($i = 0; $i < $orders_this_month; $i++) {
        // Random Day/Time in this month
        $day = rand(1, 28); // Safe bet for all months
        $hour = rand(8, 22);
        $minute = rand(0, 59);
        $second = rand(0, 59);
        $order_date = date('Y-m-d H:i:s', strtotime("$month_str-$day $hour:$minute:$second"));
        
        $user_id = $user_ids[array_rand($user_ids)];
        $status_options = ['Completed', 'Completed', 'Completed', 'Shipped', 'Pending']; // Weighted towards Completed
        $status = $status_options[array_rand($status_options)];
        
        // Ensure future dates aren't 'Completed' if we were strictly realistic, but for dummy data it's fine.
        // Or if date > now, maybe Pending? But 2025 is mostly future relative to 2024 dev, 
        // but current date in metadata is Dec 2025. So all pass.
        
        // 1. Create Order Skeleton
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, order_status, order_date, payment_method) VALUES (?, 0, ?, ?, 'Credit Card')");
        $stmt->bind_param("iss", $user_id, $status, $order_date);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        
        // 2. Add Items
        $num_items = rand(1, 3);
        $total_amount = 0;
        
        for ($j = 0; $j < $num_items; $j++) {
            $prod = $products[array_rand($products)];
            $qty = rand(1, 2);
            $price = $prod['price'];
            $line_total = $price * $qty;
            
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            $stmt_item->bind_param("iiid", $order_id, $prod['product_id'], $qty, $price);
            $stmt_item->execute();
            
            $total_amount += $line_total;
        }
        
        // 3. Update Order Total
        $stmt_update = $conn->prepare("UPDATE orders SET total_amount = ? WHERE order_id = ?");
        $stmt_update->bind_param("di", $total_amount, $order_id);
        $stmt_update->execute();
        
        $orders_created++;
    }
    
    // Next Month
    $current_date = strtotime("+1 month", $current_date);
}

echo "Successfully generated $orders_created orders across 2024-2025.\n";
echo "Done.\n";
?>
