<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

// ✅ Kiểm tra nội dung nhập vào
$question = $_POST['message'] ?? '';
if (empty($question)) {
    echo json_encode(['response' => '❌ Chưa nhập nội dung.']);
    exit;
}

// ✅ Khởi tạo lịch sử nếu chưa có
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// ✅ Thêm câu hỏi vào lịch sử
$_SESSION['chat_history'][] = [
    'role' => 'user',
    'parts' => [['text' => $question]]
];
$_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -10);

// ==========================
// 🔷 Gọi Gemini 2.5 Pro
// ==========================
function callGemini($history, $apiKey) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;
    $data = ['contents' => $history];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return ['error' => 'CURL Error: ' . curl_error($ch)];
    }
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return ['text' => $result['candidates'][0]['content']['parts'][0]['text']];
    }

    if (isset($result['error']['message'])) {
        return ['error' => $result['error']['message']];
    }

    return ['error' => 'Không có phản hồi hợp lệ từ Gemini.'];
}

// ==========================
// 🔶 Fallback: OpenRouter GPT
// ==========================
function callOpenRouter($question, $apiKey) {
    $data = [
        'model' => 'openai/gpt-3.5-turbo-16k',
        'messages' => [
            ['role' => 'system', 'content' => 'Bạn là một trợ lý AI.'],
            ['role' => 'user', 'content' => $question]
        ]
    ];

    $ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'HTTP-Referer: https://yourdomain.com', // Thay bằng domain của bạn
        'X-Title: MyChatBot'
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return '❌ CURL Error (OpenRouter): ' . curl_error($ch);
    }
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? '❌ Không có phản hồi từ OpenRouter.';
}

// ==========================
// ✅ Gọi Gemini → nếu lỗi → fallback
// ==========================
$gemini = callGemini($_SESSION['chat_history'], $GEMINI_API_KEY);

if (isset($gemini['text'])) {
    $reply = $gemini['text'];
    $_SESSION['chat_history'][] = [
        'role' => 'model',
        'parts' => [['text' => $reply]]
    ];
    echo json_encode(['response' => $reply]);
    exit;
}

// Nếu bị quá tải → fallback OpenRouter
if (str_contains(strtolower($gemini['error']), 'overloaded')) {
    $reply = callOpenRouter($question, $OPENROUTER_API_KEY);
    echo json_encode(['response' => '🤖 (Fallback GPT): ' . $reply]);
    exit;
}

// Lỗi khác
echo json_encode(['response' => '❌ Lỗi Gemini: ' . $gemini['error']]);
