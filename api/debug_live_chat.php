<?php
require_once '../LaptopAdvisor/includes/db_connect.php';

// Create a valid session/conversation first
$sessionId = 'debug_session_' . time();
$conn->query("INSERT INTO conversations (session_id, user_id, started_at) VALUES ('$sessionId', NULL, NOW())");
$conversationId = $conn->insert_id;
echo "Created conversation ID: $conversationId for session: $sessionId\n";

// Simulate a live chat request
$url = 'http://localhost/fyp/api/chat_message.php';
$data = [
    'session_id' => $sessionId,
    'message' => 'Laptop for gaming'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true // Capture error responses
    ]
];

$context  = stream_context_create($options);
echo "Sending request to $url...\n";
$result = file_get_contents($url, false, $context);

echo "Response:\n";
echo $result . "\n";
?>
