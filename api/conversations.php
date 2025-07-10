<?php
// API for Chat Conversations Management (MySQL Fixed)
// Created: 2025-07-05

session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login first.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetRequest();
            break;
        case 'POST':
            handlePostRequest();
            break;
        case 'PUT':
            handlePutRequest();
            break;
        case 'DELETE':
            handleDeleteRequest();
            break;
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}

function handleGetRequest() {
    global $conn, $user_id;
    
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            getConversationsList();
            break;
        case 'get':
            getConversationDetails();
            break;
        default:
            throw new Exception('Invalid action');
    }
}

function getConversationsList() {
    global $conn, $user_id;
    
    // Check if chat tables exist
    $table_check = $conn->query("SHOW TABLES LIKE 'chat_conversations'");
    if (!$table_check || $table_check->num_rows === 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Chat tables not found. Please run database update.',
            'conversations' => [],
            'total' => 0
        ]);
        return;
    }
    
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    $search = $_GET['search'] ?? '';
    
    $sql = "SELECT id, title, created_at, updated_at, message_count, is_archived 
            FROM chat_conversations 
            WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";
    
    if (!empty($search)) {
        $sql .= " AND title LIKE ?";
        $params[] = "%$search%";
        $types .= "s";
    }
    
    $sql .= " ORDER BY updated_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $conversations = [];
    while ($row = $result->fetch_assoc()) {
        $conversations[] = $row;
    }
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM chat_conversations WHERE user_id = ?";
    $countParams = [$user_id];
    $countTypes = "i";
    
    if (!empty($search)) {
        $countSql .= " AND title LIKE ?";
        $countParams[] = "%$search%";
        $countTypes .= "s";
    }
    
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param($countTypes, ...$countParams);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total = $countResult->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => true,
        'conversations' => $conversations,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset
    ]);
}

function getConversationDetails() {
    global $conn, $user_id;
    
    $conversation_id = intval($_GET['id'] ?? 0);
    
    if (!$conversation_id) {
        throw new Exception('Conversation ID is required');
    }
    
    // Check if conversation belongs to user
    $checkStmt = $conn->prepare("SELECT id FROM chat_conversations WHERE id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $conversation_id, $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        throw new Exception('Conversation not found');
    }
    
    // Get conversation details
    $convStmt = $conn->prepare("SELECT * FROM chat_conversations WHERE id = ?");
    $convStmt->bind_param("i", $conversation_id);
    $convStmt->execute();
    $convResult = $convStmt->get_result();
    $conversation = $convResult->fetch_assoc();
    
    // Get messages
    $msgStmt = $conn->prepare("
        SELECT id, message, response, message_type, created_at 
        FROM chat_messages 
        WHERE conversation_id = ? 
        ORDER BY created_at ASC
    ");
    $msgStmt->bind_param("i", $conversation_id);
    $msgStmt->execute();
    $msgResult = $msgStmt->get_result();
    
    $messages = [];
    while ($row = $msgResult->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'conversation' => $conversation,
        'messages' => $messages
    ]);
}

function handlePostRequest() {
    global $conn, $user_id;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'create':
            createConversation($input);
            break;
        case 'add_message':
            addMessage($input);
            break;
        default:
            throw new Exception('Invalid action');
    }
}

function createConversation($input) {
    global $conn, $user_id;
    
    // Check if chat tables exist
    $table_check = $conn->query("SHOW TABLES LIKE 'chat_conversations'");
    if (!$table_check || $table_check->num_rows === 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Chat tables not found. Please run database update first.'
        ]);
        return;
    }
    
    $title = trim($input['title'] ?? 'New Conversation');
    
    if (empty($title)) {
        $title = 'New Conversation';
    }
    
    $stmt = $conn->prepare("
        INSERT INTO chat_conversations (user_id, title, created_at, updated_at) 
        VALUES (?, ?, NOW(), NOW())
    ");
    $stmt->bind_param("is", $user_id, $title);
    $stmt->execute();
    
    $conversation_id = $conn->insert_id;
    
    echo json_encode([
        'success' => true,
        'message' => 'Conversation created successfully',
        'conversation_id' => $conversation_id
    ]);
}

function addMessage($input) {
    global $conn, $user_id;
    
    $conversation_id = intval($input['conversation_id'] ?? 0);
    $message = trim($input['message'] ?? '');
    $response = trim($input['response'] ?? '');
    
    if (!$conversation_id || empty($message)) {
        throw new Exception('Conversation ID and message are required');
    }
    
    // Check if conversation belongs to user
    $checkStmt = $conn->prepare("SELECT id FROM chat_conversations WHERE id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $conversation_id, $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        throw new Exception('Conversation not found');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Add user message
        $stmt = $conn->prepare("
            INSERT INTO chat_messages (conversation_id, user_id, message, response, message_type, created_at) 
            VALUES (?, ?, ?, ?, 'user', NOW())
        ");
        $stmt->bind_param("iiss", $conversation_id, $user_id, $message, $response);
        $stmt->execute();
        
        // Update conversation message count and title if needed
        $countStmt = $conn->prepare("SELECT message_count FROM chat_conversations WHERE id = ?");
        $countStmt->bind_param("i", $conversation_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $messageCount = $countResult->fetch_assoc()['message_count'];
        
        if ($messageCount <= 1) {
            $newTitle = substr($message, 0, 50);
            if (strlen($message) > 50) {
                $newTitle .= '...';
            }
            
            $titleStmt = $conn->prepare("UPDATE chat_conversations SET title = ?, updated_at = NOW() WHERE id = ?");
            $titleStmt->bind_param("si", $newTitle, $conversation_id);
            $titleStmt->execute();
        }
        
        // Update message count
        $updateStmt = $conn->prepare("UPDATE chat_conversations SET message_count = message_count + 1, updated_at = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $conversation_id);
        $updateStmt->execute();
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Message added successfully'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw new Exception('Failed to add message: ' . $e->getMessage());
    }
}

function handlePutRequest() {
    global $conn, $user_id;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'rename':
            renameConversation($input);
            break;
        default:
            throw new Exception('Invalid action');
    }
}

function renameConversation($input) {
    global $conn, $user_id;
    
    $conversation_id = intval($input['id'] ?? 0);
    $title = trim($input['title'] ?? '');
    
    if (!$conversation_id || empty($title)) {
        throw new Exception('Conversation ID and title are required');
    }
    
    // Check if conversation belongs to user
    $checkStmt = $conn->prepare("SELECT id FROM chat_conversations WHERE id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $conversation_id, $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        throw new Exception('Conversation not found');
    }
    
    $stmt = $conn->prepare("
        UPDATE chat_conversations 
        SET title = ?, updated_at = NOW() 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("sii", $title, $conversation_id, $user_id);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Conversation renamed successfully'
    ]);
}

function handleDeleteRequest() {
    global $conn, $user_id;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $conversation_id = intval($input['id'] ?? 0);
    
    if (!$conversation_id) {
        throw new Exception('Conversation ID is required');
    }
    
    // Check if conversation belongs to user
    $checkStmt = $conn->prepare("SELECT id FROM chat_conversations WHERE id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $conversation_id, $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        throw new Exception('Conversation not found');
    }
    
    // Delete messages first (if foreign key constraint doesn't handle cascade)
    $deleteMsgStmt = $conn->prepare("DELETE FROM chat_messages WHERE conversation_id = ?");
    $deleteMsgStmt->bind_param("i", $conversation_id);
    $deleteMsgStmt->execute();
    
    // Delete conversation
    $stmt = $conn->prepare("DELETE FROM chat_conversations WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $conversation_id, $user_id);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Conversation deleted successfully'
    ]);
}
?>