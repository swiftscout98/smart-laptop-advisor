<?php
session_start();

// Include necessary files
// Adjust paths based on location: admin/ajax/ -> ../../LaptopAdvisor/includes/
require_once '../../LaptopAdvisor/includes/config.php';
require_once '../../LaptopAdvisor/includes/ollama_client.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$perfData = $input['data'] ?? []; 

if (empty($perfData)) {
    echo json_encode(['success' => false, 'error' => 'No data provided for analysis']);
    exit();
}

try {
    // Initialize Ollama
    $ollama = new OllamaClient(OLLAMA_API_URL, OLLAMA_MODEL, OLLAMA_TIMEOUT);

    // Construct Prompt
    $promptContext = "You are an expert Data Analyst for an E-commerce Admin Dashboard.\n";
    $promptContext .= "Analyze the following performance metrics for our Recommendation Engine:\n";
    $promptContext .= "Data:\n" . json_encode($perfData, JSON_PRETTY_PRINT) . "\n\n";
    
    $promptTask = "Task: Generate exactly 2 key insights in JSON format.\n";
    $promptTask .= "1. 'Top Performer': The persona/group with best results.\n";
    $promptTask .= "2. 'Needs Attention': The persona/group with lowest results or issues.\n";
    $promptTask .= "Output purely valid JSON list of objects with keys: 'type' (success/warning), 'icon' (bi-trophy-fill/bi-exclamation-triangle-fill), 'title', 'text'.\n";
    $promptTask .= "Example JSON structure: \n";
    $promptTask .= '[{"type": "success", "icon": "bi-trophy-fill", "title": "Top Performer", "text": "Users in X group have highest satisfaction at Y%."}]';

    $response = $ollama->chat([
        ['role' => 'system', 'content' => 'You are a JSON-only API. Output ONLY valid JSON.'],
        ['role' => 'user', 'content' => $promptContext . $promptTask]
    ]);

    $jsonStr = $response['message'] ?? '[]';

    // Clean up potential markdown code blocks
    $jsonStr = preg_replace('/^```json/', '', $jsonStr);
    $jsonStr = preg_replace('/```$/', '', $jsonStr);
    $jsonStr = trim($jsonStr);

    $insights = json_decode($jsonStr, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON from AI: " . $jsonStr);
    }

    echo json_encode([
        'success' => true,
        'insights' => $insights
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'AI Generation Failed: ' . $e->getMessage()
    ]);
}
?>
