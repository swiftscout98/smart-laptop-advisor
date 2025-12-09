<?php
// ============================================
// Conversation Logs - Enhanced ASUS-Style
// Module D: Smart Laptop Advisor Admin
// ============================================

require_once 'includes/db_connect.php';

// ============================================
// LOGIC SECTION
// ============================================

// Fetch conversation statistics
$stats_query = "SELECT 
    COUNT(*) as total_conversations,
    COUNT(CASE WHEN DATE(started_at) = CURDATE() THEN 1 END) as today_count,
    COUNT(CASE WHEN DATE(started_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as week_count,
    AVG(CASE WHEN sentiment = 'positive' THEN 1 ELSE 0 END) * 100 as satisfaction_rate,
    AVG(message_count) as avg_messages_per_session,
    AVG(duration_seconds) as avg_duration
FROM conversations";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Handle filters
$sentiment_filter = isset($_GET['sentiment']) ? $_GET['sentiment'] : 'all';
$time_filter = isset($_GET['time']) ? $_GET['time'] : 'all';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build WHERE clause
$where_conditions = [];
$params = [];
$types = '';

if ($sentiment_filter !== 'all') {
    $where_conditions[] = "c.sentiment = ?";
    $params[] = $sentiment_filter;
    $types .= 's';
}

if ($time_filter !== 'all') {
    switch ($time_filter) {
        case 'today':
            $where_conditions[] = "DATE(c.started_at) = CURDATE()";
            break;
        case 'week':
            $where_conditions[] = "c.started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $where_conditions[] = "c.started_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
}

if (!empty($search_term)) {
    $where_conditions[] = "(c.session_id LIKE ? OR u.full_name LIKE ? OR c.user_ip LIKE ? OR c.customer_email LIKE ?)";
    $search_param = "%{$search_term}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Fetch conversations
$conversations_query = "SELECT 
    c.conversation_id,
    c.session_id,
    c.source,
    c.started_at,
    c.duration_seconds,
    c.message_count,
    c.user_message_count,
    c.bot_message_count,
    c.sentiment,
    c.outcome,
    c.satisfaction_rating,
    c.customer_email,
    COALESCE(u.full_name, 'Anonymous User') as user_name,
    COALESCE(u.email, c.user_ip) as user_identifier
FROM conversations c
LEFT JOIN users u ON c.user_id = u.user_id
{$where_clause}
ORDER BY c.started_at DESC
LIMIT 50";

if (!empty($params)) {
    $stmt = $conn->prepare($conversations_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $conversations_result = $stmt->get_result();
} else {
    $conversations_result = $conn->query($conversations_query);
}

$conversations = [];
while ($row = $conversations_result->fetch_assoc()) {
    $conversations[] = $row;
}

// Get hourly distribution for today
$hourly_query = "SELECT HOUR(started_at) as hour, COUNT(*) as count 
                 FROM conversations 
                 WHERE DATE(started_at) = CURDATE() 
                 GROUP BY HOUR(started_at)";
$hourly_result = $conn->query($hourly_query);
$hourly_data = array_fill(0, 24, 0);
while ($row = $hourly_result->fetch_assoc()) {
    $hourly_data[$row['hour']] = $row['count'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation Logs - Smart Laptop Advisor</title>
    
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="source/assets/css/bootstrap.css">
    <link rel="stylesheet" href="source/assets/vendors/iconly/bold.css">
    <link rel="stylesheet" href="source/assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="source/assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="source/assets/css/app.css">
    <link rel="shortcut icon" href="source/assets/images/favicon.svg" type="image/x-icon">
    
    <style>
    :root {
        --asus-primary: #0d6efd;
        --asus-secondary: #6c63ff;
        --asus-success: #10b981;
        --asus-warning: #f59e0b;
        --asus-danger: #ef4444;
        --asus-dark: #1e293b;
        --asus-light: #f8fafc;
        --asus-gradient: linear-gradient(135deg, #0d6efd 0%, #6c63ff 100%);
    }
    
    /* Stats Cards - ASUS Style */
    .stats-card {
        background: var(--asus-light);
        border-radius: 16px;
        padding: 24px;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stats-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }
    
    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--asus-gradient);
    }
    
    .stats-card .stats-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }
    
    .stats-card .stats-value {
        font-size: 2rem;
        font-weight: 800;
        color: var(--asus-dark);
        line-height: 1;
    }
    
    .stats-card .stats-label {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 4px;
    }
    
    .stats-card .stats-change {
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 20px;
        font-weight: 600;
    }
    
    .stats-change.positive { background: rgba(16, 185, 129, 0.1); color: var(--asus-success); }
    .stats-change.negative { background: rgba(239, 68, 68, 0.1); color: var(--asus-danger); }
    
    /* Conversation Table */
    .conversation-table {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    
    .conversation-table .table {
        margin-bottom: 0;
    }
    
    .conversation-table thead {
        background: var(--asus-light);
    }
    
    .conversation-table thead th {
        border: none;
        font-weight: 700;
        color: var(--asus-dark);
        padding: 16px 20px;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .conversation-table tbody tr {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .conversation-table tbody tr:hover {
        background: #f1f5f9;
    }
    
    .conversation-table tbody td {
        padding: 16px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
    }
    
    /* User Avatar */
    .user-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        color: white;
    }
    
    /* Sentiment Badge */
    .sentiment-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .sentiment-badge.positive { background: rgba(16, 185, 129, 0.1); color: var(--asus-success); }
    .sentiment-badge.neutral { background: rgba(100, 116, 139, 0.1); color: #64748b; }
    .sentiment-badge.negative { background: rgba(239, 68, 68, 0.1); color: var(--asus-danger); }
    
    /* Duration Badge */
    .duration-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        background: rgba(13, 110, 253, 0.1);
        color: var(--asus-primary);
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    /* Filter Bar */
    .filter-bar {
        background: white;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }
    
    .filter-bar .form-control,
    .filter-bar .form-select {
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        padding: 10px 16px;
        transition: all 0.2s ease;
    }
    
    .filter-bar .form-control:focus,
    .filter-bar .form-select:focus {
        border-color: var(--asus-primary);
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }
    
    .filter-bar .btn {
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
    }
    
    /* ============================================
       ENHANCED CHAT PREVIEW PANEL - Modern Design
       ============================================ */
    .chat-preview-panel {
        position: fixed;
        right: -520px;
        top: 0;
        width: 520px;
        height: 100vh;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        box-shadow: -8px 0 40px rgba(0, 0, 0, 0.2);
        transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1050;
        display: flex;
        flex-direction: column;
        border-left: 1px solid rgba(0,0,0,0.05);
    }
    
    .chat-preview-panel.active {
        right: 0;
    }
    
    /* Chat Header - Modern Gradient */
    .chat-preview-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        color: white;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }
    
    .chat-preview-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        animation: pulse-slow 4s ease-in-out infinite;
    }
    
    @keyframes pulse-slow {
        0%, 100% { transform: scale(1); opacity: 0.1; }
        50% { transform: scale(1.2); opacity: 0.2; }
    }
    
    .chat-preview-header .header-content {
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 1;
    }
    
    .chat-preview-header .bot-avatar-header {
        width: 44px;
        height: 44px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    
    .chat-preview-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .chat-preview-header .header-subtitle {
        font-size: 0.75rem;
        opacity: 0.85;
        margin-top: 2px;
    }
    
    .chat-preview-close {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 1;
    }
    
    .chat-preview-close:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: rotate(90deg);
    }
    
    /* Chat Info Bar - Glassmorphism */
    .chat-preview-info {
        padding: 16px 20px;
        background: white;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .chat-preview-info .info-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-radius: 25px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    
    .chat-preview-info .info-chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .chat-preview-info .info-chip i {
        font-size: 0.9rem;
    }
    
    .chat-preview-info .info-chip.sentiment-positive {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        color: #059669;
        border-color: #a7f3d0;
    }
    
    .chat-preview-info .info-chip.sentiment-negative {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        color: #dc2626;
        border-color: #fecaca;
    }
    
    .chat-preview-info .info-chip.sentiment-neutral {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        color: #64748b;
        border-color: #cbd5e1;
    }
    
    /* Chat Body - Message Container */
    .chat-preview-body {
        flex: 1;
        overflow-y: auto;
        padding: 24px 20px;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        scroll-behavior: smooth;
    }
    
    .chat-preview-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .chat-preview-body::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .chat-preview-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    
    .chat-preview-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Date Separator */
    .chat-date-separator {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 24px 0;
    }
    
    .chat-date-separator span {
        background: white;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
    }
    
    /* Chat Message Container */
    .chat-message {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        animation: messageSlideIn 0.3s ease-out;
    }
    
    @keyframes messageSlideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .chat-message.user {
        flex-direction: row-reverse;
    }
    
    .chat-message.bot {
        flex-direction: row;
    }
    
    /* Message Avatar */
    .message-avatar {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        margin-top: 4px;
    }
    
    .chat-message.bot .message-avatar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .chat-message.user .message-avatar {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    
    /* Message Content Wrapper */
    .message-content {
        display: flex;
        flex-direction: column;
        max-width: 85%;
    }
    
    .chat-message.user .message-content {
        align-items: flex-end;
    }
    
    .chat-message.bot .message-content {
        align-items: flex-start;
    }
    
    /* Chat Bubble - Enhanced */
    .chat-bubble {
        padding: 14px 18px;
        border-radius: 20px;
        font-size: 0.9rem;
        line-height: 1.6;
        position: relative;
        word-wrap: break-word;
    }
    
    .chat-message.user .chat-bubble {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom-right-radius: 6px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.25);
    }
    
    .chat-message.bot .chat-bubble {
        background: white;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 6px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    
    /* Bot Message Content Formatting */
    .chat-message.bot .chat-bubble h1,
    .chat-message.bot .chat-bubble h2,
    .chat-message.bot .chat-bubble h3,
    .chat-message.bot .chat-bubble strong {
        color: #1e293b;
        font-weight: 700;
    }
    
    .chat-message.bot .chat-bubble ul,
    .chat-message.bot .chat-bubble ol {
        margin: 8px 0;
        padding-left: 20px;
    }
    
    .chat-message.bot .chat-bubble li {
        margin-bottom: 4px;
    }
    
    /* Product Recommendation Cards in Bot Message */
    .bot-product-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        margin: 10px 0;
        display: flex;
        gap: 12px;
        transition: all 0.2s ease;
    }
    
    .bot-product-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }
    
    .bot-product-card .product-img {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    
    .bot-product-card .product-info {
        flex: 1;
    }
    
    .bot-product-card .product-name {
        font-weight: 700;
        font-size: 0.85rem;
        color: #1e293b;
        margin-bottom: 2px;
    }
    
    .bot-product-card .product-price {
        font-weight: 800;
        font-size: 0.9rem;
        color: #667eea;
    }
    
    .bot-product-card .product-specs {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 4px;
    }
    
    /* Table Styling in Bot Messages */
    .chat-message.bot .chat-bubble table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
        font-size: 0.8rem;
        background: #f8fafc;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .chat-message.bot .chat-bubble th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
    }
    
    .chat-message.bot .chat-bubble td {
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .chat-message.bot .chat-bubble tr:last-child td {
        border-bottom: none;
    }
    
    .chat-message.bot .chat-bubble tr:hover td {
        background: #f1f5f9;
    }
    
    /* Message Time */
    .chat-time {
        font-size: 0.7rem;
        color: #94a3b8;
        margin-top: 6px;
        padding: 0 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .chat-message.user .chat-time {
        justify-content: flex-end;
    }
    
    .chat-time .read-status {
        color: #667eea;
    }
    
    /* Intent Badge */
    .intent-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
        color: #7c3aed;
        border-radius: 15px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-top: 6px;
    }
    
    /* Typing Indicator */
    .typing-indicator {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 12px 18px;
        background: white;
        border-radius: 20px;
        border-bottom-left-radius: 6px;
        border: 1px solid #e2e8f0;
    }
    
    .typing-indicator span {
        width: 8px;
        height: 8px;
        background: #94a3b8;
        border-radius: 50%;
        animation: typingBounce 1.4s infinite ease-in-out;
    }
    
    .typing-indicator span:nth-child(1) { animation-delay: 0s; }
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
    
    @keyframes typingBounce {
        0%, 60%, 100% { transform: translateY(0); background: #94a3b8; }
        30% { transform: translateY(-4px); background: #667eea; }
    }
    
    /* Chat Footer - Enhanced */
    .chat-preview-footer {
        padding: 20px 24px;
        background: white;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 12px;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
    }
    
    .chat-preview-footer .btn {
        flex: 1;
        padding: 14px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .chat-preview-footer .btn-outline-warning {
        border: 2px solid #f59e0b;
        color: #f59e0b;
        background: transparent;
    }
    
    .chat-preview-footer .btn-outline-warning:hover {
        background: #f59e0b;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }
    
    .chat-preview-footer .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
    }
    
    .chat-preview-footer .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .chat-preview-footer .btn-outline-secondary {
        border: 2px solid #64748b;
        color: #64748b;
        background: transparent;
    }
    
    .chat-preview-footer .btn-outline-secondary:hover {
        background: #64748b;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(100, 116, 139, 0.3);
    }
    
    /* Response Time Badge */
    .response-time-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        border-radius: 10px;
        font-size: 0.65rem;
        font-weight: 600;
        margin-left: 8px;
    }
    
    /* Empty State */
    .chat-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding: 40px;
        text-align: center;
    }
    
    .chat-empty-state i {
        font-size: 4rem;
        color: #e2e8f0;
        margin-bottom: 16px;
    }
    
    .chat-empty-state h6 {
        color: #64748b;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .chat-empty-state p {
        color: #94a3b8;
        font-size: 0.85rem;
    }
    
    /* Activity Heatmap */
    .activity-heatmap {
        display: flex;
        gap: 4px;
        padding: 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    
    .heatmap-hour {
        flex: 1;
        height: 40px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        color: white;
        font-weight: 600;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .heatmap-hour:hover {
        transform: scaleY(1.2);
    }
    
    /* Overlay */
    .panel-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .panel-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    /* Quick Stats Row */
    .quick-stat {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: var(--asus-light);
        border-radius: 10px;
        font-size: 0.85rem;
    }
    
    .quick-stat i {
        font-size: 1.1rem;
    }
    
    /* Message Count Badge */
    .msg-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        background: var(--asus-light);
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--asus-dark);
    }
    
    /* Page Title */
    .page-title-box {
        background: var(--asus-gradient);
        color: white;
        padding: 30px;
        border-radius: 20px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .page-title-box::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-title-box h3 {
        font-size: 1.75rem;
        font-weight: 800;
        margin-bottom: 8px;
    }
    
    .page-title-box p {
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    /* Action Button */
    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #e2e8f0;
        background: white;
        color: var(--asus-dark);
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .action-btn:hover {
        background: var(--asus-primary);
        border-color: var(--asus-primary);
        color: white;
    }
    </style>
</head>

<body>
    <div id="app">
        <?php include 'includes/admin_header.php'; ?>

        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <!-- Page Title -->
            <div class="page-title-box">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3><i class="bi bi-chat-square-text me-2"></i>Conversation Logs</h3>
                        <p>Monitor and analyze chatbot interactions in real-time</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-light" onclick="exportLogs()">
                            <i class="bi bi-download me-2"></i>Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4 mb-xl-0">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon me-3" style="background: var(--asus-gradient);">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <div>
                                <div class="stats-value"><?php echo number_format($stats['total_conversations']); ?></div>
                                <div class="stats-label">Total Conversations</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4 mb-xl-0">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon me-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div>
                                <div class="stats-value"><?php echo number_format($stats['today_count']); ?></div>
                                <div class="stats-label">Today's Chats</div>
                            </div>
                        </div>
                        <span class="stats-change positive position-absolute" style="top: 16px; right: 16px;">
                            <i class="bi bi-arrow-up"></i> Active
                        </span>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4 mb-md-0">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon me-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                <i class="bi bi-emoji-smile"></i>
                            </div>
                            <div>
                                <div class="stats-value"><?php echo number_format($stats['satisfaction_rate'], 0); ?>%</div>
                                <div class="stats-label">Satisfaction Rate</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon me-3" style="background: linear-gradient(135deg, #6c63ff 0%, #5046e5 100%);">
                                <i class="bi bi-chat-left-text"></i>
                            </div>
                            <div>
                                <div class="stats-value"><?php echo number_format($stats['avg_messages_per_session'], 1); ?></div>
                                <div class="stats-label">Avg Messages/Chat</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Heatmap -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-graph-up me-2"></i>Today's Activity</h5>
                    <small class="text-muted">Conversations per hour</small>
                </div>
                <div class="card-body">
                    <div class="activity-heatmap">
                        <?php 
                        $maxHourly = max($hourly_data) ?: 1;
                        for ($h = 0; $h < 24; $h++): 
                            $intensity = $hourly_data[$h] / $maxHourly;
                            $hue = 220; // Blue base
                            $saturation = 80;
                            $lightness = 90 - ($intensity * 50);
                        ?>
                            <div class="heatmap-hour" 
                                 style="background: hsl(<?php echo $hue; ?>, <?php echo $saturation; ?>%, <?php echo $lightness; ?>%); color: <?php echo $intensity > 0.5 ? 'white' : '#1e293b'; ?>"
                                 title="<?php echo str_pad($h, 2, '0', STR_PAD_LEFT); ?>:00 - <?php echo $hourly_data[$h]; ?> chats">
                                <?php echo $hourly_data[$h] > 0 ? $hourly_data[$h] : ''; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="d-flex justify-content-between mt-2 text-muted" style="font-size: 0.7rem;">
                        <span>12 AM</span>
                        <span>6 AM</span>
                        <span>12 PM</span>
                        <span>6 PM</span>
                        <span>11 PM</span>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <form method="GET" class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" name="search" 
                                   placeholder="Search by session, user, email..." 
                                   value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="sentiment" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $sentiment_filter === 'all' ? 'selected' : ''; ?>>All Sentiment</option>
                            <option value="positive" <?php echo $sentiment_filter === 'positive' ? 'selected' : ''; ?>>😊 Positive</option>
                            <option value="neutral" <?php echo $sentiment_filter === 'neutral' ? 'selected' : ''; ?>>😐 Neutral</option>
                            <option value="negative" <?php echo $sentiment_filter === 'negative' ? 'selected' : ''; ?>>😞 Negative</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="time" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $time_filter === 'all' ? 'selected' : ''; ?>>All Time</option>
                            <option value="today" <?php echo $time_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                            <option value="week" <?php echo $time_filter === 'week' ? 'selected' : ''; ?>>This Week</option>
                            <option value="month" <?php echo $time_filter === 'month' ? 'selected' : ''; ?>>This Month</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-2"></i>Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="admin_conversation_logs.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Conversations Table -->
            <div class="conversation-table">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Session</th>
                                <th>Started</th>
                                <th>Duration</th>
                                <th>Messages</th>
                                <th>Sentiment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($conversations) > 0): ?>
                                <?php foreach ($conversations as $conv): ?>
                                    <?php
                                    $minutes = floor($conv['duration_seconds'] / 60);
                                    $seconds = $conv['duration_seconds'] % 60;
                                    $duration_display = $minutes > 0 ? "{$minutes}m {$seconds}s" : "{$seconds}s";
                                    
                                    $avatar_colors = ['#0d6efd', '#6c63ff', '#10b981', '#f59e0b', '#ef4444'];
                                    $avatar_color = $avatar_colors[crc32($conv['user_name']) % count($avatar_colors)];
                                    $initials = strtoupper(substr($conv['user_name'], 0, 2));
                                    
                                    $sentiment_icons = [
                                        'positive' => '😊',
                                        'neutral' => '😐',
                                        'negative' => '😞'
                                    ];
                                    ?>
                                    <tr onclick="viewConversation(<?php echo $conv['conversation_id']; ?>)">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3" style="background: <?php echo $avatar_color; ?>;">
                                                    <?php echo $initials; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($conv['user_name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($conv['customer_email'] ?: $conv['user_identifier']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code style="font-size: 0.75rem;"><?php echo htmlspecialchars(substr($conv['session_id'], 0, 12)); ?>...</code>
                                        </td>
                                        <td>
                                            <div><?php echo date('M d, Y', strtotime($conv['started_at'])); ?></div>
                                            <small class="text-muted"><?php echo date('h:i A', strtotime($conv['started_at'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="duration-badge">
                                                <i class="bi bi-clock"></i>
                                                <?php echo $duration_display; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="msg-count"><?php echo $conv['message_count']; ?></span>
                                        </td>
                                        <td>
                                            <span class="sentiment-badge <?php echo $conv['sentiment'] ?: 'neutral'; ?>">
                                                <?php echo $sentiment_icons[$conv['sentiment']] ?? '😐'; ?>
                                                <?php echo ucfirst($conv['sentiment'] ?: 'Neutral'); ?>
                                            </span>
                                        </td>
                                        <td onclick="event.stopPropagation();">
                                            <button class="action-btn me-1" onclick="viewConversation(<?php echo $conv['conversation_id']; ?>)" title="View">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="action-btn me-1" onclick="flagConversation(<?php echo $conv['conversation_id']; ?>)" title="Flag">
                                                <i class="bi bi-flag"></i>
                                            </button>
                                            <button class="action-btn" onclick="exportSingle(<?php echo $conv['conversation_id']; ?>)" title="Export">
                                                <i class="bi bi-download"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="mt-3 mb-0 text-muted">No conversations found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Chat Preview Panel - Enhanced Design -->
    <div class="panel-overlay" onclick="closeChatPreview()"></div>
    <div class="chat-preview-panel" id="chatPreviewPanel">
        <div class="chat-preview-header">
            <div class="header-content">
                <div class="bot-avatar-header">
                    <i class="bi bi-robot"></i>
                </div>
                <div>
                    <h5>Conversation Preview</h5>
                    <div class="header-subtitle" id="conversationSubtitle">Loading...</div>
                </div>
            </div>
            <button class="chat-preview-close" onclick="closeChatPreview()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="chat-preview-info" id="chatPreviewInfo">
            <!-- Info chips loaded via AJAX -->
        </div>
        <div class="chat-preview-body" id="chatPreviewBody">
            <!-- Messages loaded via AJAX -->
            <div class="chat-empty-state">
                <i class="bi bi-chat-square-dots"></i>
                <h6>Select a Conversation</h6>
                <p>Click on any conversation in the table to view the chat history</p>
            </div>
        </div>
        <div class="chat-preview-footer">
            <button class="btn btn-outline-warning" onclick="flagCurrentConversation()">
                <i class="bi bi-flag me-2"></i>Flag
            </button>
            <button class="btn btn-outline-secondary" onclick="analyzeConversation()">
                <i class="bi bi-graph-up me-2"></i>Analyze
            </button>
            <button class="btn btn-primary" onclick="exportCurrentConversation()">
                <i class="bi bi-download me-2"></i>Export
            </button>
        </div>
    </div>

    <?php include 'includes/admin_footer.php'; ?>

    <script>
    let currentConversationId = null;
    let currentConversation = null;
    
    // View Conversation in Side Panel
    function viewConversation(conversationId) {
        currentConversationId = conversationId;
        
        // Show panel with animation
        document.getElementById('chatPreviewPanel').classList.add('active');
        document.querySelector('.panel-overlay').classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Show loading state
        document.getElementById('conversationSubtitle').textContent = 'Loading...';
        document.getElementById('chatPreviewInfo').innerHTML = `
            <div class="info-chip"><i class="bi bi-hourglass-split"></i> Loading details...</div>
        `;
        document.getElementById('chatPreviewBody').innerHTML = `
            <div class="chat-empty-state">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                <h6>Loading Conversation</h6>
                <p>Please wait while we fetch the chat history...</p>
            </div>
        `;
        
        // Fetch conversation details
        fetch(`ajax/get_conversation_details.php?id=${conversationId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentConversation = data.conversation;
                    displayConversation(data.conversation, data.messages);
                } else {
                    showError('Error loading conversation');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to load conversation. Please try again.');
            });
    }
    
    function showError(message) {
        document.getElementById('chatPreviewBody').innerHTML = `
            <div class="chat-empty-state">
                <i class="bi bi-exclamation-triangle text-warning"></i>
                <h6>Something went wrong</h6>
                <p>${message}</p>
                <button class="btn btn-sm btn-primary mt-2" onclick="viewConversation(${currentConversationId})">
                    <i class="bi bi-arrow-clockwise me-1"></i> Retry
                </button>
            </div>
        `;
    }
    
    function displayConversation(conversation, messages) {
        // Update header subtitle
        const formattedDate = conversation.started_formatted || conversation.started_at || 'Unknown date';
        document.getElementById('conversationSubtitle').textContent = 
            `${conversation.user_name} • ${formattedDate}`;
        
        // Build info chips
        const sentimentEmoji = {
            'positive': '😊',
            'neutral': '😐', 
            'negative': '😞'
        };
        const sentiment = conversation.sentiment || 'neutral';
        const duration = formatDuration(conversation.duration_seconds);
        
        document.getElementById('chatPreviewInfo').innerHTML = `
            <div class="info-chip">
                <i class="bi bi-person"></i> ${escapeHtml(conversation.user_name)}
            </div>
            <div class="info-chip">
                <i class="bi bi-clock"></i> ${duration}
            </div>
            <div class="info-chip">
                <i class="bi bi-chat-dots"></i> ${conversation.message_count || messages.length} messages
            </div>
            <div class="info-chip sentiment-${sentiment}">
                ${sentimentEmoji[sentiment]} ${sentiment.charAt(0).toUpperCase() + sentiment.slice(1)}
            </div>
            ${conversation.outcome ? `<div class="info-chip"><i class="bi bi-bullseye"></i> ${escapeHtml(conversation.outcome)}</div>` : ''}
        `;
        
        // Build messages with enhanced formatting
        let messagesHtml = '';
        let lastDateStr = null;
        
        messages.forEach((msg, index) => {
            // Extract date from timestamp (format: "Nov 24, 2025 at 3:45 PM")
            const timestampParts = (msg.timestamp || '').split(' at ');
            const dateStr = timestampParts[0] || '';
            
            // Add date separator if new day
            if (dateStr && lastDateStr !== dateStr) {
                messagesHtml += `
                    <div class="chat-date-separator">
                        <span>${dateStr}</span>
                    </div>
                `;
                lastDateStr = dateStr;
            }
            
            const isUser = msg.message_type === 'user';
            const avatarIcon = isUser ? 'bi-person' : 'bi-robot';
            const timeStr = msg.time_only || timestampParts[1] || '';
            const formattedContent = isUser ? escapeHtml(msg.message_content) : formatBotMessage(msg.message_content);
            
            // Calculate response time for bot messages
            let responseTimeBadge = '';
            if (!isUser && msg.response_time_display) {
                responseTimeBadge = `<span class="response-time-badge"><i class="bi bi-lightning"></i> ${msg.response_time_display}</span>`;
            }
            
            // Intent badge for bot messages
            let intentBadge = '';
            if (!isUser && msg.intent_detected) {
                intentBadge = `<div class="intent-badge"><i class="bi bi-cpu"></i> ${escapeHtml(msg.intent_detected)}</div>`;
            }
            
            messagesHtml += `
                <div class="chat-message ${isUser ? 'user' : 'bot'}" style="animation-delay: ${index * 0.05}s">
                    <div class="message-avatar">
                        <i class="bi ${avatarIcon}"></i>
                    </div>
                    <div class="message-content">
                        <div class="chat-bubble">${formattedContent}</div>
                        <div class="chat-time">
                            ${timeStr}
                            ${isUser ? '<i class="bi bi-check2-all read-status"></i>' : ''}
                            ${responseTimeBadge}
                        </div>
                        ${intentBadge}
                    </div>
                </div>
            `;
        });
        
        document.getElementById('chatPreviewBody').innerHTML = messagesHtml || `
            <div class="chat-empty-state">
                <i class="bi bi-chat-square"></i>
                <h6>No Messages</h6>
                <p>This conversation has no messages to display</p>
            </div>
        `;
        
        // Smooth scroll to top (to show from beginning)
        setTimeout(() => {
            const body = document.getElementById('chatPreviewBody');
            body.scrollTop = 0;
        }, 100);
    }
    
    // Format bot messages with enhanced styling
    function formatBotMessage(content) {
        if (!content) return '';
        
        // First decode HTML entities that might have been double-encoded
        let formatted = content
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&amp;/g, '&')
            .replace(/&quot;/g, '"')
            .replace(/&#039;/g, "'");
        
        // Check if content has markdown tables
        if (formatted.includes('|') && formatted.includes('---')) {
            formatted = formatMarkdownTable(formatted);
        } else {
            // Escape HTML for non-table content
            formatted = escapeHtml(formatted);
        }
        
        // Convert **bold** to <strong>
        formatted = formatted.replace(/\*\*([^*]+)\*\*/g, '<strong class="text-primary">$1</strong>');
        
        // Convert markdown-style headers
        formatted = formatted.replace(/^### (.+)$/gm, '<h6 class="mt-3 mb-2 fw-bold">$1</h6>');
        formatted = formatted.replace(/^## (.+)$/gm, '<h5 class="mt-3 mb-2 fw-bold">$1</h5>');
        
        // Convert bullet points (• - *)
        formatted = formatted.replace(/^[•●] (.+)$/gm, '<li style="margin-left: 16px;">$1</li>');
        formatted = formatted.replace(/^- (.+)$/gm, '<li style="margin-left: 16px;">$1</li>');
        
        // Convert numbered lists
        formatted = formatted.replace(/^\d+\.\s+(.+)$/gm, '<li style="margin-left: 16px;">$1</li>');
        
        // Convert line breaks
        formatted = formatted.replace(/\n\n/g, '</p><p class="mb-2">');
        formatted = formatted.replace(/\n/g, '<br>');
        
        // Handle <br> tags in content
        formatted = formatted.replace(/&lt;br&gt;/gi, '<br>');
        formatted = formatted.replace(/<br>/gi, '<br>');
        
        // Wrap in paragraph if needed
        if (!formatted.startsWith('<')) {
            formatted = `<p class="mb-0">${formatted}</p>`;
        }
        
        return formatted;
    }
    
    // Format markdown tables into HTML
    function formatMarkdownTable(content) {
        const lines = content.split('\n');
        let result = [];
        let inTable = false;
        let tableHtml = '';
        let headers = [];
        
        lines.forEach((line, index) => {
            const trimmed = line.trim();
            
            // Check if this is a table row
            if (trimmed.startsWith('|') && trimmed.endsWith('|')) {
                // Skip separator row (|---|---|)
                if (trimmed.match(/^\|[\s\-:|]+\|$/)) {
                    return;
                }
                
                const cells = trimmed.split('|').filter(c => c.trim() !== '');
                
                if (!inTable) {
                    // Start table - this is the header row
                    inTable = true;
                    headers = cells.map(c => c.trim());
                    tableHtml = '<div class="table-responsive mt-2 mb-2"><table class="table table-sm table-bordered mb-0" style="font-size: 0.8rem;"><thead class="table-primary"><tr>';
                    headers.forEach(h => {
                        tableHtml += `<th class="py-2 px-2">${escapeHtml(h)}</th>`;
                    });
                    tableHtml += '</tr></thead><tbody>';
                } else {
                    // Data row
                    tableHtml += '<tr>';
                    cells.forEach((c, i) => {
                        let cellContent = c.trim();
                        // Format bold text in cells
                        cellContent = cellContent.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
                        tableHtml += `<td class="py-2 px-2">${cellContent}</td>`;
                    });
                    tableHtml += '</tr>';
                }
            } else {
                // Not a table row
                if (inTable) {
                    // Close the table
                    tableHtml += '</tbody></table></div>';
                    result.push(tableHtml);
                    tableHtml = '';
                    inTable = false;
                }
                result.push(escapeHtml(trimmed));
            }
        });
        
        // Close any open table
        if (inTable) {
            tableHtml += '</tbody></table></div>';
            result.push(tableHtml);
        }
        
        return result.join('\n');
    }
    
    
    // Helper: Format date for display
    function formatDate(date) {
        const options = { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' };
        return date.toLocaleDateString('en-US', options);
    }
    
    // Helper: Format date label
    function formatDateLabel(date) {
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        
        if (date.toDateString() === today.toDateString()) {
            return 'Today';
        } else if (date.toDateString() === yesterday.toDateString()) {
            return 'Yesterday';
        } else {
            return date.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
        }
    }
    
    // Helper: Format time
    function formatTime(date) {
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }
    
    // Helper: Format duration
    function formatDuration(seconds) {
        if (!seconds || seconds < 0) return '0s';
        if (seconds < 60) return `${seconds}s`;
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        if (minutes < 60) return `${minutes}m ${secs}s`;
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        return `${hours}h ${mins}m`;
    }
    
    function closeChatPreview() {
        document.getElementById('chatPreviewPanel').classList.remove('active');
        document.querySelector('.panel-overlay').classList.remove('active');
        document.body.style.overflow = '';
        currentConversationId = null;
        currentConversation = null;
    }
    
    function flagConversation(id) {
        const reason = prompt('Enter reason for flagging (optional):');
        if (reason !== null) {
            fetch('ajax/flag_conversation.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({conversation_id: id, action: 'flag', reason: reason})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', 'Conversation flagged successfully!');
                } else {
                    showToast('error', 'Error: ' + data.message);
                }
            });
        }
    }
    
    function flagCurrentConversation() {
        if (currentConversationId) flagConversation(currentConversationId);
    }
    
    function analyzeConversation() {
        if (!currentConversation) {
            showToast('warning', 'Please select a conversation first');
            return;
        }
        
        // Show analysis modal or panel
        const analysis = {
            sentiment: currentConversation.sentiment || 'neutral',
            messages: currentConversation.message_count || 0,
            duration: formatDuration(currentConversation.duration_seconds),
            outcome: currentConversation.outcome || 'Unknown'
        };
        
        alert(`📊 Conversation Analysis\n\n` +
              `Sentiment: ${analysis.sentiment.charAt(0).toUpperCase() + analysis.sentiment.slice(1)}\n` +
              `Messages: ${analysis.messages}\n` +
              `Duration: ${analysis.duration}\n` +
              `Outcome: ${analysis.outcome}`);
    }
    
    function exportLogs() {
        const urlParams = new URLSearchParams(window.location.search);
        window.location.href = `ajax/export_conversations.php?${urlParams.toString()}`;
    }
    
    function exportSingle(id) {
        window.location.href = `ajax/export_conversations.php?conversation_id=${id}`;
    }
    
    function exportCurrentConversation() {
        if (currentConversationId) {
            exportSingle(currentConversationId);
        } else {
            showToast('warning', 'Please select a conversation first');
        }
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Toast notification helper
    function showToast(type, message) {
        // Use existing toast system if available, otherwise use alert
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type,
                title: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert(message);
        }
    }
    
    // Close panel on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeChatPreview();
    });
    
    // Add hover effect to table rows
    document.querySelectorAll('.conversation-table tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.005)';
        });
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    </script>
</body>
</html>