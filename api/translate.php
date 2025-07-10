<?php
// api/translate.php

session_start();
require_once '../config.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// 🔐 Lấy dữ liệu đầu vào
$text = $_POST['text'] ?? '';
$source = $_POST['source_lang'] ?? 'auto';
$target = $_POST['target_lang'] ?? 'vi';

if (trim($text) === '') {
    echo json_encode(['error' => 'Bạn chưa nhập nội dung cần dịch.']);
    exit;
}

// 🔗 Gọi Google Translate API
$url = 'https://translation.googleapis.com/language/translate/v2';

$data = [
    'q' => $text,
    'source' => $source === 'auto' ? null : $source,
    'target' => $target,
    'format' => 'text',
    'key' => $GOOGLE_TRANSLATE_API_KEY
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($data),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
]);

$response = curl_exec($ch);
curl_close($ch);

$json = json_decode($response, true);

// ✅ Kiểm tra phản hồi API
if (isset($json['data']['translations'][0]['translatedText'])) {
    $translatedText = $json['data']['translations'][0]['translatedText'];
    
    $detectedLang = $json['data']['translations'][0]['detectedSourceLanguage'] ?? null;
    // 🧠 Nếu user đã đăng nhập → lưu lịch sử dịch
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("INSERT INTO translations (user_id, original, translated, source_lang, target_lang) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $_SESSION['user_id'], $text, $translatedText, $source, $target);
        $stmt->execute();
        $stmt->close();
    }

    // ✅ Trả kết quả dịch kèm ngôn ngữ phát hiện nếu có
    $response = ['translated' => $translatedText];
    if ($source === 'auto' && $detectedLang) {
        $response['detected'] = $detectedLang;
    }
    echo json_encode($response);

} else {
    echo json_encode(['error' => '❌ Không thể dịch. Kiểm tra API key hoặc quota.']);
}
// Kết thúc script
?>