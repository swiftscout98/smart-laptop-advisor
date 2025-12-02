<?php
/**
 * Chat Message API Endpoint
 * Handles user messages, retrieves conversation history, calls Ollama, and saves responses
 */

header('Content-Type: application/json');

require_once '../LaptopAdvisor/includes/db_connect.php';
require_once '../LaptopAdvisor/includes/config.php';
require_once '../LaptopAdvisor/includes/ollama_client.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (!isset($input['session_id']) || !isset($input['message'])) {
        throw new Exception('Missing required parameters: session_id and message');
    }
    
    $sessionId = trim($input['session_id']);
    $userMessage = trim($input['message']);
    
    // Validate message is not empty
    if (empty($userMessage)) {
        throw new Exception('Message cannot be empty');
    }
    
    // Verify session exists and get conversation ID
    $stmt = $conn->prepare("SELECT conversation_id FROM conversations WHERE session_id = ?");
    $stmt->bind_param("s", $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Invalid session ID');
    }
    
    $conversation = $result->fetch_assoc();
    $conversationId = $conversation['conversation_id'];
    $stmt->close();
    
    // Retrieve recent conversation history
    $stmt = $conn->prepare("SELECT message_type, message_content FROM conversation_messages WHERE conversation_id = ? ORDER BY timestamp DESC LIMIT ?");
    $historyLimit = CONVERSATION_HISTORY_LIMIT;
    $stmt->bind_param("ii", $conversationId, $historyLimit);
    $stmt->execute();
    $historyResult = $stmt->get_result();
    
    // Build messages array for Ollama (reverse order - oldest first)
    $messages = [];
    
    // Add system prompt
    $messages[] = [
        'role' => 'system',
        'content' => SYSTEM_PROMPT
    ];
    
    // Add conversation history (reverse the order)
    $historyMessages = [];
    while ($row = $historyResult->fetch_assoc()) {
        $historyMessages[] = [
            'role' => $row['message_type'] === 'user' ? 'user' : 'assistant',
            'content' => $row['message_content']
        ];
    }
    $stmt->close();
    
    // Reverse to get chronological order
    $messages = array_merge($messages, array_reverse($historyMessages));
    
    // ========================================
    // DYNAMIC COMMAND INJECTION
    // Retrieve active intents and inject as AI instructions
    // ========================================
    $intentSql = "SELECT intent_name, description, 
                  (SELECT response_text FROM intent_responses WHERE intent_id = i.intent_id AND is_default = 1 LIMIT 1) as default_response 
                  FROM intents i WHERE is_active = 1 ORDER BY priority DESC";
    $intentResult = $conn->query($intentSql);
    
    $dynamicRules = "";
    if ($intentResult && $intentResult->num_rows > 0) {
        $dynamicRules = "\n\n=== DYNAMIC COMMANDS ===\n";
        $dynamicRules .= "The following are specific instructions you MUST follow when users ask about these topics:\n\n";
        
        while($row = $intentResult->fetch_assoc()) {
            $intentName = $row['intent_name'];
            $description = $row['description'];
            $defaultResponse = $row['default_response'];
            
            // Build dynamic rule for this intent
            if (!empty($defaultResponse)) {
                $dynamicRules .= "- When user asks about '{$intentName}'";
                if (!empty($description)) {
                    $dynamicRules .= " ({$description})";
                }
                $dynamicRules .= ", you MUST include this information: \"{$defaultResponse}\"\n";
            }
        }
        
        $dynamicRules .= "\nThese commands have been configured by the administrator and take precedence over general knowledge.\n";
        $dynamicRules .= "=== END OF DYNAMIC COMMANDS ===\n";
    }
    
    // Inject dynamic rules into system prompt
    if (!empty($dynamicRules)) {
        $messages[0]['content'] = SYSTEM_PROMPT . $dynamicRules;
    }
    
    // ========================================
    // RAG: Fetch Relevant Products from Database
    // ========================================
    $productContext = fetchRelevantProducts($conn, $userMessage);
    
    // Inject product context into system message if products were found
    if (!empty($productContext)) {
        // Update the system message to include product catalog
        $messages[0]['content'] = SYSTEM_PROMPT . "\n\n" . $productContext;
    }
    
    // Add new user message
    $messages[] = [
        'role' => 'user',
        'content' => $userMessage
    ];
    
    // Save user message to database
    $stmt = $conn->prepare("INSERT INTO conversation_messages (conversation_id, message_type, message_content, timestamp) VALUES (?, 'user', ?, NOW())");
    $stmt->bind_param("is", $conversationId, $userMessage);
    $stmt->execute();
    $userMessageId = $stmt->insert_id; // Capture ID for intent tracking
    $stmt->close();
    
    // ========================================
    // SENTIMENT ANALYSIS (LLM-as-a-Judge)
    // Analyze user message sentiment for analytics
    // ========================================
    $ollama = new OllamaClient(OLLAMA_API_URL, OLLAMA_MODEL, OLLAMA_TIMEOUT);

    if (strlen($userMessage) > 10) {
        $analysis = $ollama->analyzeSentiment($userMessage);
        
        if ($analysis && isset($analysis['sentiment']) && isset($analysis['score'])) {
            $stmt = $conn->prepare("UPDATE conversations SET sentiment = ?, satisfaction_rating = ? WHERE conversation_id = ?");
            $stmt->bind_param("sii", $analysis['sentiment'], $analysis['score'], $conversationId);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Call Ollama API
    $aiResponse = $ollama->chat($messages);
    
    if (!$aiResponse['success']) {
        // Save error to database as bot message
        $errorMessage = "I apologize, but I'm having trouble connecting to my AI service right now. Please try again in a moment. Error: " . $aiResponse['message'];
        
        $stmt = $conn->prepare("INSERT INTO conversation_messages (conversation_id, message_type, message_content, timestamp, response_time_ms) VALUES (?, 'bot', ?, NOW(), ?)");
        $stmt->bind_param("isi", $conversationId, $errorMessage, $aiResponse['response_time']);
        $stmt->execute();
        $stmt->close();
        
        throw new Exception($aiResponse['message']);
    }
    
    // Save bot response to database
    $botMessage = $aiResponse['message'];
    $responseTime = $aiResponse['response_time'];

    $stmt = $conn->prepare("INSERT INTO conversation_messages (conversation_id, message_type, message_content, timestamp, response_time_ms) VALUES (?, 'bot', ?, NOW(), ?)");
    $stmt->bind_param("isi", $conversationId, $botMessage, $responseTime);
    $stmt->execute();
    $stmt->close();
    
    // ========================================
    // INTENT USAGE TRACKING
    // Track which intents were used in this response
    // ========================================
    trackIntentByResponse($conn, $botMessage, $userMessageId);

    // Return success response to frontend
    echo json_encode([
        'success' => true,
        'response' => $botMessage,
        'response_time' => $responseTime,
        'conversation_id' => $conversationId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();

/**
 * RAG Helper Function: Fetch Relevant Products
 * Retrieves products from database based on user query context
 */
function fetchRelevantProducts($conn, $userMessage) {
    // Extract budget if mentioned
    $budget = null;
    if (preg_match('/\$?\s*(\d{3,5})\s*(?:dollar|usd)?/i', $userMessage, $matches)) {
        $budget = (int)$matches[1];
    }
    
    // Detect use case keywords
    $useCase = null;
    $keywords = [
        'gaming' => ['gaming', 'game', 'gamer', 'play', 'rtx', 'gpu'],
        'creative' => ['creative', 'design', 'video', 'editing', 'photoshop', 'content', 'creator', 'render'],
        'business' => ['business', 'work', 'professional', 'office', 'productivity', 'excel', 'powerpoint'],
        'student' => ['student', 'school', 'study', 'education', 'homework', 'college', 'university']
    ];
    
    foreach ($keywords as $case => $wordList) {
        foreach ($wordList as $word) {
            if (stripos($userMessage, $word) !== false) {
                $useCase = $case;
                break 2;
            }
        }
    }
    
    // Check if user is asking about a specific product/brand
    $specificProduct = null;
    if (preg_match('/(macbook|apple|asus|hp|lenovo|dell|msi|thinkpad|zenbook|ideapad|spectre|victus|pavilion)/i', $userMessage, $matches)) {
        $specificProduct = $matches[1];
    }
    
    // Build SQL query
    $conditions = [];
    $params = [];
    $types = '';
    
    // If asking about specific brand/product, prioritize that
    if ($specificProduct) {
        $conditions[] = "(brand LIKE ? OR product_name LIKE ?)";
        $searchTerm = "%{$specificProduct}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    
    if ($budget) {
        $conditions[] = "price <= ?";
        $params[] = $budget;
        $types .= 'd';
    }
    
    if ($useCase && !$specificProduct) {
        // Only filter by use case if not asking about specific product
        $conditions[] = "primary_use_case = ?";
        $params[] = $useCase;
        $types .= 's';
    }
    
    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    
    // Fetch products
    $query = "SELECT product_id, product_name, brand, price, cpu, gpu, ram_gb, storage_gb, 
              storage_type, display_size, description, primary_use_case 
              FROM products 
              {$whereClause}
              ORDER BY price ASC 
              LIMIT 8";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // No filters - return all products (general inquiry)
        $result = $conn->query($query);
    }
    
    // If no results with strict filters, try again without use case filter
    if ($result->num_rows === 0 && $useCase) {
        // Retry without use case restriction
        $conditions = [];
        $params = [];
        $types = '';
        
        if ($budget) {
            $conditions[] = "price <= ?";
            $params[] = $budget;
            $types .= 'd';
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $query = "SELECT product_id, product_name, brand, price, cpu, gpu, ram_gb, storage_gb, 
                  storage_type, display_size, description, primary_use_case 
                  FROM products 
                  {$whereClause}
                  ORDER BY price ASC 
                  LIMIT 8";
        
        if (!empty($params)) {
            if (isset($stmt)) $stmt->close();
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($query);
        }
    }
    
    if ($result->num_rows === 0) {
        return ''; // No products found, AI will respond generally
    }
    
    // Format products for AI context
    $productList = "\n\n=== AVAILABLE LAPTOP INVENTORY ===\n";
    $productList .= "IMPORTANT: You MUST ONLY recommend laptops from this list. DO NOT suggest any other products.\n\n";
    
    $count = 0;
    while ($product = $result->fetch_assoc()) {
        $count++;
        $productList .= "{$count}. **{$product['brand']} {$product['product_name']}** - \${$product['price']}\n";
        $productList .= "   CPU: {$product['cpu']}\n";
        $productList .= "   GPU: " . ($product['gpu'] ?? 'Integrated') . "\n";
        $productList .= "   RAM: {$product['ram_gb']} GB\n";
        $productList .= "   Storage: {$product['storage_gb']} GB {$product['storage_type']}\n";
        $productList .= "   Display: {$product['display_size']}\" screen\n";
        $productList .= "   Best For: " . ucfirst($product['primary_use_case']) . "\n";
        if (!empty($product['description'])) {
            $productList .= "   Details: {$product['description']}\n";
        }
        $productList .= "\n";
    }
    
    $productList .= "=== END OF INVENTORY ({$count} products shown) ===\n";
    $productList .= "Remember: Only recommend from the above list. If asked about a product not listed, say we don't currently stock it but suggest similar alternatives from our inventory.\n";
    
    if (isset($stmt)) {
        $stmt->close();
    }
    
    return $productList;
}

/**
 * Intent Tracking Function
 * Detects which intent was used based on bot response content
 * Updates usage statistics automatically
 */
function trackIntentByResponse($conn, $botResponse, $userMessageId) {
    // Get all active intents with their default responses
    $query = "SELECT i.intent_id, i.intent_name, ir.response_text 
              FROM intents i
              JOIN intent_responses ir ON i.intent_id = ir.intent_id
              WHERE i.is_active = 1 AND ir.is_default = 1";
    
    $result = $conn->query($query);
    
    if (!$result) {
        return; // Query failed, skip tracking
    }
    
    while ($row = $result->fetch_assoc()) {
        $defaultResponse = $row['response_text'];
        $similarity = 0;
        
        // Check if bot response contains parts of the default response
        similar_text($defaultResponse, $botResponse, $similarity);
        
        // If at least 30% similarity, consider this intent as triggered
        if ($similarity > 30) {
            // Intent was used! Update statistics
            $stmt = $conn->prepare("UPDATE intents 
                                    SET usage_count = usage_count + 1,
                                        success_count = success_count + 1,
                                        last_used_at = NOW()
                                    WHERE intent_id = ?");
            $stmt->bind_param("i", $row['intent_id']);
            $stmt->execute();
            $stmt->close();

            // Update conversation message with detected intent and confidence
            $confidence = $similarity / 100;
            $stmt = $conn->prepare("UPDATE conversation_messages 
                                    SET intent_detected = ?, 
                                        intent_confidence = ? 
                                    WHERE message_id = ?");
            $stmt->bind_param("sdi", $row['intent_name'], $confidence, $userMessageId);
            $stmt->execute();
            $stmt->close();

            break; // Only track one intent per response
        }
    }
}
?>
