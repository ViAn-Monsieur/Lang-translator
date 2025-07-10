<?php session_start(); ?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/functions.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="translator-container">
  <h2 class="title" style="text-align:center">🌐 Trình Dịch Ngôn Ngữ</h2>

  <!-- Bộ chọn ngôn ngữ -->
  <div class="language-select-row">
    <div class="lang-box">
      <label for="sourceLang">Ngôn ngữ nguồn</label>
      <select id="sourceLang" class="lang-select">
        <option value="auto">Tự động</option>
        <option value="en">English</option>
        <option value="vi">Vietnamese</option>
        <option value="ja">Japanese</option>
        <option value="zh">Chinese (Simplified)</option>
        <option value="zh-TW">Chinese (Traditional)</option>
        <option value="ko">Korean</option>
        <option value="fr">French</option>
        <option value="de">German</option>
        <option value="ru">Russian</option>
        <option value="th">Thai</option>
        <option value="es">Spanish</option>
      </select>
    </div>

    <button id="swapLang" class="swap-btn" title="Đảo ngôn ngữ"><i class="fas fa-arrows-rotate"></i></button>

    <div class="lang-box">
      <label for="targetLang">Ngôn ngữ đích</label>
      <select id="targetLang" class="lang-select">
        <option value="vi">Vietnamese</option>
        <option value="en">English</option>
        <option value="ja">Japanese</option>
        <option value="zh">Chinese (Simplified)</option>
        <option value="zh-TW">Chinese (Traditional)</option>
        <option value="ko">Korean</option>
        <option value="fr">French</option>
        <option value="de">German</option>
        <option value="ru">Russian</option>
        <option value="th">Thai</option>
        <option value="es">Spanish</option>
      </select>
    </div>
  </div>

  <!-- Vùng nhập và kết quả -->
  <div class="translation-panels">
    <!-- Panel input -->
    <div class="panel input-panel">
      <textarea id="inputText" class="text-input" placeholder="Nhập nội dung hoặc nhấn mic 🎹 để nói..."></textarea>
      <div class="panel-controls">
        <button id="btnSpeakInput" title="Nói" onclick="startListening()"><i class="fas fa-microphone"></i></button>
        <button id="btnReadInput" title="Đọc gốc" onclick="speakOriginal()"><i class="fas fa-volume-up"></i></button>
        <button id="btnClearInput" title="Xoá nội dung" onclick="document.getElementById('inputText').value = '';" ><i class="fas fa-times"></i></button>
        <button id="btnCopyInput" title="Sao chép nội dung" onclick="copyText('inputText')"><i class="fas fa-copy"></i></button>
      </div>
    </div>

    <!-- Panel kết quả -->
    <div class="panel result-panel" style="position:relative;">
      <div id="loadingIndicator" class="dot-loading" style="display:none;">
        <span></span><span></span><span></span>
      </div>
      <textarea id="result" class="text-result" placeholder="Bản dịch sẽ hiển thị ở đây..." readonly></textarea>
      <div class="panel-controls">
        <button id="btnReadResult" title="Đọc bản dịch" onclick="speakTranslated()"><i class="fas fa-volume-up"></i></button>
        <button id="btnCopyResult" title="Sao chép bản dịch" onclick="copyText('result')"><i class="fas fa-copy"></i></button>
        <div class="toggle-wrapper">
          <label class="switch">
            <input type="checkbox" id="autoSpeakToggle" checked>
            <span class="slider round"></span>
          </label>
          <span class="toggle-label">Tự động đọc bản dịch</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Lịch sử dịch -->
  <div class="history-section">
    <?php
    if (isset($_SESSION['user_id'])) {
        echo "<h3>Lịch sử dịch của bạn</h3>";
        require 'includes/db.php';
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT * FROM translations WHERE user_id = ? ORDER BY created_at DESC LIMIT 7");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "<ul class='translation-history'>";
            while ($row = $result->fetch_assoc()) {
                echo "<li><strong>" . htmlspecialchars($row['original']) . "</strong> → " . htmlspecialchars($row['translated']) . " <em>(" . htmlspecialchars($row['created_at']) . ")</em></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Chưa có lịch sử dịch.</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Đăng nhập để xem lịch sử dịch của bạn.</p>";
    }
    ?>
  </div>
</div>

<!-- Scripts -->
<script src="js/voice.js"></script>

<?php include 'includes/footer.php'; ?>
