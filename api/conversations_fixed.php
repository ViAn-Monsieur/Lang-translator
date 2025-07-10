<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "Chưa đăng nhập"]);
    exit;
}

if ($method === 'GET') {
    if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'get') {
        // 📨 Lấy tin nhắn theo conversation_id
        $conversation_id = intval($_GET['id']);

        // Chỉ lấy tin nhắn nếu thuộc về user hiện tại
        $stmt = $conn->prepare("SELECT * FROM chat_messages 
                                WHERE conversation_id = ? AND user_id = ?
                                ORDER BY created_at ASC");
        $stmt->bind_param("ii", $conversation_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode(["success" => true, "messages" => $messages]);
        exit;
    } else {
        // 📂 Lấy danh sách các cuộc trò chuyện của user hiện tại
        $stmt = $conn->prepare("SELECT * FROM chat_conversations 
                                WHERE user_id = ? 
                                ORDER BY updated_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $conversations = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode(["success" => true, "conversations" => $conversations]);
        exit;
    }
}

if ($method === 'POST') {
    if ($data['action'] === 'create') {
        $title = $data['title'] ?? 'Cuộc trò chuyện mới';
        $stmt = $conn->prepare("INSERT INTO chat_conversations (user_id, title) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $title);
        $stmt->execute();

        echo json_encode(["success" => true, "conversation_id" => $conn->insert_id]);
        exit;
    }

    if ($data['action'] === 'add_message') {
        $conversation_id = intval($data['conversation_id']);
        $message = $data['message'] ?? '';
        $responseMsg = $data['response'] ?? '';

        $stmt = $conn->prepare("INSERT INTO chat_messages 
            (conversation_id, user_id, message, response, message_type) 
            VALUES (?, ?, ?, ?, 'user')");
        $stmt->bind_param("iiss", $conversation_id, $user_id, $message, $responseMsg);
        $stmt->execute();

        // Cập nhật số lượng và thời gian
        $stmt = $conn->prepare("UPDATE chat_conversations 
                                SET message_count = message_count + 1, updated_at = NOW() 
                                WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $conversation_id, $user_id);
        $stmt->execute();

        echo json_encode(["success" => true]);
        exit;
    }
}

if ($method === 'PUT') {
    if ($data['action'] === 'rename' && isset($data['id'], $data['title'])) {
        $id = intval($data['id']);
        $title = $data['title'];

        $stmt = $conn->prepare("UPDATE chat_conversations 
                                SET title = ? 
                                WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $title, $id, $user_id);
        $stmt->execute();

        echo json_encode(["success" => true]);
        exit;
    }
}

if ($method === 'DELETE') {
    if ($data['action'] === 'delete' && isset($data['id'])) {
        $id = intval($data['id']);

        // Xoá tin nhắn trước
        $stmt = $conn->prepare("DELETE FROM chat_messages WHERE conversation_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();

        // Xoá cuộc trò chuyện
        $stmt = $conn->prepare("DELETE FROM chat_conversations WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();

        echo json_encode(["success" => true]);
        exit;
    }
}

// ❌ Hành động không hợp lệ
echo json_encode(["success" => false, "message" => "Hành động không hợp lệ"]);
exit;
