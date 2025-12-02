<?php
require_once '../LaptopAdvisor/includes/db_connect.php';

// Mock data
$userInput = "Laptop for gaming";
$userMessageId = 99999; // Dummy ID

echo "Testing Intent Tracking for input: '$userInput'\n";

// Copy of the function from chat_message.php
function debugTrackIntentByInput($conn, $userInput, $userMessageId) {
    echo "Function called...\n";
    
    // Get all active intents with their training phrases
    $query = "SELECT t.phrase_text, i.intent_id, i.intent_name 
              FROM training_phrases t 
              JOIN intents i ON t.intent_id = i.intent_id 
              WHERE i.is_active = 1";
    
    $result = $conn->query($query);
    
    if (!$result) {
        echo "Query failed: " . $conn->error . "\n";
        return;
    }
    
    echo "Found " . $result->num_rows . " training phrases.\n";
    
    $bestMatchIntentId = null;
    $bestMatchIntentName = null;
    $highestSimilarity = 0;

    while ($row = $result->fetch_assoc()) {
        $phrase = $row['phrase_text'];
        
        // Debug output for specific phrases
        if (stripos($phrase, 'gaming') !== false) {
             echo "Checking against phrase: '$phrase' (Intent: {$row['intent_name']})\n";
        }

        // 1. Check for direct containment (stripos) - Strongest Match
        if (stripos($userInput, $phrase) !== false) {
            echo "MATCH FOUND! '$userInput' contains '$phrase'\n";
            $bestMatchIntentId = $row['intent_id'];
            $bestMatchIntentName = $row['intent_name'];
            $highestSimilarity = 100;
            break; // Stop immediately on exact phrase match
        }
        
        // 2. Check similarity
        similar_text(strtolower($userInput), strtolower($phrase), $percent);
        if ($percent > $highestSimilarity) {
            $highestSimilarity = $percent;
            $bestMatchIntentId = $row['intent_id'];
            $bestMatchIntentName = $row['intent_name'];
        }
    }

    echo "Best Match: " . ($bestMatchIntentName ?? 'None') . " (Similarity: $highestSimilarity%)\n";

    // Threshold for fuzzy match (e.g., > 60% similarity)
    if ($bestMatchIntentId && $highestSimilarity > 60) {
        echo "Updating DB for Intent ID: $bestMatchIntentId\n";
        
        // Check current count
        $res = $conn->query("SELECT usage_count FROM intents WHERE intent_id = $bestMatchIntentId");
        $row = $res->fetch_assoc();
        echo "Current Usage Count: " . $row['usage_count'] . "\n";

        // Intent was used! Update statistics
        $stmt = $conn->prepare("UPDATE intents 
                                SET usage_count = usage_count + 1,
                                    success_count = success_count + 1,
                                    last_used_at = NOW()
                                WHERE intent_id = ?");
        $stmt->bind_param("i", $bestMatchIntentId);
        if ($stmt->execute()) {
             echo "Update executed successfully.\n";
        } else {
             echo "Update failed: " . $stmt->error . "\n";
        }
        $stmt->close();
        
        // Verify update
        $res = $conn->query("SELECT usage_count FROM intents WHERE intent_id = $bestMatchIntentId");
        $row = $res->fetch_assoc();
        echo "New Usage Count: " . $row['usage_count'] . "\n";

    } else {
        echo "No match above threshold.\n";
    }
}

debugTrackIntentByInput($conn, $userInput, $userMessageId);
?>
