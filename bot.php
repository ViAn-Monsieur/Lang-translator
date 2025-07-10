<?php 
session_start(); 
include 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chatbot Dịch Ngôn Ngữ</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* Chat page specific styles */
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      overflow: hidden;
    }
    
    .chat-layout {
      display: flex;
      height: 100vh;
      transition: margin-left var(--transition-normal);
    }
    
    .chat-layout.sidebar-open {
      margin-left: 350px;
    }
    
    .chat-page-container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 0;
      background: var(--bg-light);
    }

    .chat-container {
      width: 800px;
      max-width: 95vw;
      height: 600px;
      max-height: 90vh;
      margin: 0;
    }

    .chat-body {
      display: flex;
      flex-direction: column;
    }

    /* RESET: Override all conflicting CSS from style.css */
    .message {
      background: none !important;
      max-width: none !important;
      padding: 0 !important;
      align-self: unset !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
      border-radius: 0 !important;
      
      /* New flexbox container styles */
      display: flex !important;
      margin: var(--spacing-md) 0 !important;
      align-items: flex-start !important;
      width: 100% !important;
    }

    .message.user {
      justify-content: flex-end !important;
    }

    .message.bot {
      justify-content: flex-start !important;
    }

    /* Message content - the actual bubble */
    .message-content {
      width: fit-content;
      max-width: 75%;
      min-width: 60px;
      padding: var(--spacing-sm) var(--spacing-md);
      border-radius: var(--border-radius-lg);
      line-height: 1.4;
      word-break: break-word;
      animation: messageSlideIn 0.3s ease;
      text-align: left;
      box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .message.user .message-content {
      background-color: var(--bg-chat-user);
      border-bottom-right-radius: var(--border-radius-sm);
    }

    .message.bot .message-content {
      background-color: var(--bg-chat-bot);
      border-bottom-left-radius: var(--border-radius-sm);
    }

    .message-time {
      font-size: var(--font-size-xs);
      color: var(--text-muted);
      margin-top: var(--spacing-xs);
    }

    .typing-indicator {
      display: flex;
      align-items: center;
      padding: var(--spacing-sm) var(--spacing-md);
      background-color: var(--bg-chat-bot);
      border-radius: var(--border-radius-lg);
      border-bottom-left-radius: var(--border-radius-sm);
      margin: var(--spacing-md) 0;
      max-width: 80px;
    }

    .typing-dots {
      display: flex;
      gap: 4px;
    }

    .typing-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background-color: var(--text-muted);
      animation: typingDot 1.4s infinite ease-in-out;
    }

    .typing-dot:nth-child(1) { animation-delay: -0.32s; }
    .typing-dot:nth-child(2) { animation-delay: -0.16s; }

    @keyframes typingDot {
      0%, 80%, 100% {
        transform: scale(0);
        opacity: 0.5;
      }
      40% {
        transform: scale(1);
        opacity: 1;
      }
    }

    /* Login prompt styles */
    .login-prompt {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: var(--spacing-md);
      border-radius: var(--border-radius);
      margin-bottom: var(--spacing-md);
      text-align: center;
    }

    .login-prompt a {
      color: white;
      text-decoration: underline;
      font-weight: 600;
    }

    /* Conversation history styles */
    .conversation-history {
      max-height: 400px;
      overflow-y: auto;
    }
    
    .conversation-item {
      padding: var(--spacing-sm) var(--spacing-md);
      border-bottom: 1px solid var(--border-color);
      cursor: pointer;
      transition: background-color 0.2s;
      position: relative;
    }
    
    .conversation-item:hover {
      background-color: var(--bg-secondary);
    }
    
    .conversation-item.active {
      background-color: var(--bg-primary);
      color: white;
    }
    
    .conversation-title {
      font-weight: 600;
      margin-bottom: 4px;
      font-size: var(--font-size-sm);
    }
    
    .conversation-date {
      font-size: var(--font-size-xs);
      color: var(--text-muted);
    }
    
    .conversation-item.active .conversation-date {
      color: rgba(255, 255, 255, 0.8);
    }
    
    .conversation-actions {
      position: absolute;
      right: var(--spacing-md);
      top: 50%;
      transform: translateY(-50%);
      opacity: 0;
      transition: opacity 0.2s;
    }
    
    .conversation-item:hover .conversation-actions {
      opacity: 1;
    }
    
    .conversation-actions button {
      background: none;
      border: none;
      padding: 4px;
      cursor: pointer;
      border-radius: 4px;
      font-size: 14px;
    }
    
    .conversation-actions button:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }
    
    /* Sidebar header improvements */
    .sidebar-header {
      display: flex !important;
      flex-direction: row !important;
      align-items: center !important;
      justify-content: space-between !important;
      padding: var(--spacing-md) !important;
      gap: var(--spacing-sm) !important;
    }
    
    .sidebar-title h4 {
      margin: 0 !important;
      font-size: 16px;
      font-weight: 600;
      white-space: nowrap;
    }
    
    .new-chat-btn {
      white-space: nowrap;
      font-size: 12px !important;
      padding: 6px 12px !important;
      border-radius: 20px !important;
      font-weight: 500;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .chat-layout.sidebar-open {
        margin-left: 0;
      }
      
      .chat-page-container {
        padding: 0;
      }
      
      .chat-container {
        width: 100vw;
        height: 100vh;
        border-radius: 0;
      }
      
      .message-content {
        max-width: 85% !important;
      }
    }
    
    @media (max-width: 480px) {
      .message-content {
        max-width: 90% !important;
        min-width: 50px !important;
        padding: 8px 12px !important;
      }
      
      .sidebar-title h4 {
        font-size: 14px !important;
      }
      
      .new-chat-btn {
        font-size: 11px !important;
        padding: 4px 8px !important;
      }
    }
    
    @media (max-width: 350px) {
      .message-content {
        max-width: 95% !important;
        min-width: 40px !important;
        padding: 6px 10px !important;
        font-size: 14px !important;
      }
      
      .sidebar-title h4 {
        font-size: 13px !important;
      }
      
      .new-chat-btn {
        font-size: 10px !important;
        padding: 3px 6px !important;
      }
    }
  </style>
</head>
<body>

<div class="chat-layout" id="chatLayout">
  <div class="chat-page-container">
  <div class="chat-container">
    <div class="chat-header">
      <div class="d-flex align-items-center">
        <button id="sidebarToggle" class="btn btn-sm btn-outline-light me-3">
          ☰
        </button>
        <span>🤖 Dịch Ngôn Ngữ AI</span>
      </div>
      <div class="d-flex align-items-center">
        <?php if (isset($_SESSION['user_id'])): ?>
          <span class="text-light me-3">Xin chào, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong></span>
          <a href="index.php" class="btn btn-sm btn-outline-light">
            🚪 Về trang chính
          </a>
        <?php else: ?>
          <a href="login.php" class="btn btn-sm btn-outline-light">
            🔑 Đăng nhập
          </a>
        <?php endif; ?>
      </div>
    </div>

    <div id="chatBody" class="chat-body">
      <?php if (!isset($_SESSION['user_id'])): ?>
      <div class="login-prompt">
        <h4>💬 Chào mừng đến với AI Chat!</h4>
        <p>🔐 <strong>Đăng nhập</strong> để lưu lịch sử cuộc trò chuyện và sử dụng sidebar</p>
        <p>Hoặc tiếp tục chat mà không cần đăng nhập (không lưu lịch sử)</p>
        <div class="mt-3">
          <a href="login.php" class="btn btn-sm btn-light me-2">🔑 Đăng nhập</a>
          <a href="register.php" class="btn btn-sm btn-outline-light">📝 Đăng ký</a>
        </div>
      </div>
      <?php endif; ?>
      
      <div class="text-center text-muted p-4">
        <h4>🎉 Bắt đầu cuộc trò chuyện!</h4>
        <p>Bạn có thể:</p>
        <ul class="list-unstyled">
          <li>💬 Trò chuyện với AI</li>
          <li>🌍 Dịch ngôn ngữ</li>
          <li>❓ Đặt câu hỏi bất kỳ</li>
          <li>📚 Học hỏi kiến thức mới</li>
        </ul>
      </div>
    </div>

    <div class="chat-input">
      <input type="text" id="messageInput" class="form-control" 
             placeholder="Nhập tin nhắn của bạn..." 
             onkeypress="handleKeyPress(event)">
      <button onclick="sendMessage()" class="btn btn-primary btn-round" id="sendButton">
        <span id="sendButtonText">Gửi</span>
        <span id="sendButtonLoader" class="d-none">⏳</span>
      </button>
    </div>
  </div>
  </div>
</div>

<!-- Chat History Sidebar -->
<div class="sidebar" id="chatSidebar">
  <div class="sidebar-header">
    <div class="sidebar-title">
      <h4 class="mb-0">💬 Lịch sử Chat</h4>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
      <button class="btn btn-sm btn-primary new-chat-btn" onclick="createNewConversation()">
        ➕ Tạo mới
      </button>
    <?php else: ?>
      <button class="btn btn-sm btn-primary" onclick="showLoginPrompt()">
        🔑 Đăng nhập
      </button>
    <?php endif; ?>
  </div>
  <div class="sidebar-body">
    <div id="conversationsList">
      <?php if (!isset($_SESSION['user_id'])): ?>
      <div class="text-center p-3">
        <div class="login-prompt mb-3">
          <p class="mb-2">🔐 <strong>Cần đăng nhập</strong></p>
          <p class="text-sm">Đăng nhập để lưu và quản lý lịch sử cuộc trò chuyện</p>
        </div>
        <a href="login.php" class="btn btn-sm btn-primary me-2">🔑 Đăng nhập</a>
        <a href="register.php" class="btn btn-sm btn-outline-primary">📝 Đăng ký</a>
      </div>
      <?php else: ?>
      <div id="conversationHistory" class="conversation-history">
        <div class="text-center p-3">
          <p class="text-muted">Đang tải lịch sử...</p>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
// Chat functionality
let isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
let currentConversationId = null;
let conversationHistory = [];

function handleKeyPress(event) {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault();
    sendMessage();
  }
}

async function sendMessage() {
  const input = document.getElementById('messageInput');
  const text = input.value.trim();
  
  if (!text) return;
  
  // Disable input and show loading
  setLoading(true);
  input.value = '';
  
  // Add user message to chat
  addMessage(text, 'user');
  
  // Show typing indicator
  showTypingIndicator();
  
  try {
    // Create new conversation if none exists and user is logged in
    if (isLoggedIn && !currentConversationId) {
      const createResponse = await fetch('api/conversations.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'create',
          title: text.substring(0, 50) + (text.length > 50 ? '...' : '')
        })
      });
      
      const createData = await createResponse.json();
      if (createData.success) {
        currentConversationId = createData.conversation_id;
      }
    }
    
    // Send to AI
    const response = await fetch('gemini_translate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'message=' + encodeURIComponent(text)
    });
    
    const data = await response.json();
    const aiResponse = data.response || 'Xin lỗi, tôi không thể phản hồi lúc này.';
    
    // Remove typing indicator
    hideTypingIndicator();
    
    // Add AI response
    addMessage(aiResponse, 'bot');
    
    // Save conversation if logged in
    if (isLoggedIn && currentConversationId) {
      await saveMessageToConversation(text, aiResponse);
    }
    
  } catch (error) {
    console.error('Error sending message:', error);
    hideTypingIndicator();
    addMessage('❌ Lỗi kết nối. Vui lòng thử lại.', 'bot');
  } finally {
    setLoading(false);
  }
}

function addMessage(text, sender) {
  const chatBody = document.getElementById('chatBody');
  const messageDiv = document.createElement('div');
  messageDiv.className = `message ${sender}`;
  
  const time = new Date().toLocaleTimeString('vi-VN', { 
    hour: '2-digit', 
    minute: '2-digit' 
  });
  
  messageDiv.innerHTML = `
    <div class="message-content">
      ${escapeHtml(text)}
      <div class="message-time">${time}</div>
    </div>
  `;
  
  chatBody.appendChild(messageDiv);
  chatBody.scrollTop = chatBody.scrollHeight;
}

function showTypingIndicator() {
  const chatBody = document.getElementById('chatBody');
  const typingDiv = document.createElement('div');
  typingDiv.className = 'message bot';
  typingDiv.id = 'typingIndicator';
  typingDiv.innerHTML = `
    <div class="typing-indicator">
      <div class="typing-dots">
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
      </div>
    </div>
  `;
  
  chatBody.appendChild(typingDiv);
  chatBody.scrollTop = chatBody.scrollHeight;
}

function hideTypingIndicator() {
  const typingIndicator = document.getElementById('typingIndicator');
  if (typingIndicator) {
    typingIndicator.remove();
  }
}

function setLoading(loading) {
  const input = document.getElementById('messageInput');
  const button = document.getElementById('sendButton');
  const buttonText = document.getElementById('sendButtonText');
  const buttonLoader = document.getElementById('sendButtonLoader');
  
  input.disabled = loading;
  button.disabled = loading;
  
  if (loading) {
    buttonText.classList.add('d-none');
    buttonLoader.classList.remove('d-none');
  } else {
    buttonText.classList.remove('d-none');
    buttonLoader.classList.add('d-none');
    input.focus();
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function showLoginPrompt() {
  alert('Vui lòng đăng nhập để sử dụng tính năng lưu lịch sử cuộc trò chuyện!\n\nClick "🔑 Đăng nhập" để chuyển đến trang đăng nhập.');
}

// Conversation management functions
async function loadConversationHistory() {
  if (!isLoggedIn) return;
  
  try {
    const response = await fetch('api/conversations.php?action=list&limit=20');
    const data = await response.json();
    
    if (data.success) {
      conversationHistory = data.conversations;
      renderConversationHistory();
    }
  } catch (error) {
    console.error('Error loading conversation history:', error);
  }
}

function renderConversationHistory() {
  const historyContainer = document.getElementById('conversationHistory');
  
  if (conversationHistory.length === 0) {
    historyContainer.innerHTML = `
      <div class="text-center p-3">
        <p class="text-muted">Chưa có cuộc trò chuyện nào</p>
        <small class="text-muted">Bắt đầu chat để tạo cuộc trò chuyện đầu tiên!</small>
      </div>
    `;
    return;
  }
  
  const historyHTML = conversationHistory.map(conv => {
    const isActive = conv.id === currentConversationId;
    const date = new Date(conv.updated_at).toLocaleDateString('vi-VN');
    
    return `
      <div class="conversation-item ${isActive ? 'active' : ''}" onclick="loadConversation(${conv.id})">
        <div class="conversation-title">${escapeHtml(conv.title)}</div>
        <div class="conversation-date">${date}</div>
        <div class="conversation-actions">
          <button class="btn-sm" onclick="event.stopPropagation(); deleteConversation(${conv.id})" title="Xóa">
            🗑️
          </button>
        </div>
      </div>
    `;
  }).join('');
  
  historyContainer.innerHTML = historyHTML;
}

async function createNewConversation() {
  if (!isLoggedIn) {
    showLoginPrompt();
    return;
  }
  
  try {
    const response = await fetch('api/conversations.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'create',
        title: 'Cuộc trò chuyện mới'
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      currentConversationId = data.conversation_id;
      
      // Clear current chat
      const chatBody = document.getElementById('chatBody');
      chatBody.innerHTML = `
        <div class="text-center text-muted p-4">
          <h4>🎉 Cuộc trò chuyện mới!</h4>
          <p>Hãy bắt đầu bằng cách gửi tin nhắn đầu tiên.</p>
        </div>
      `;
      
      // Reload conversation history
      await loadConversationHistory();
      
      // Close sidebar on mobile
      closeSidebar();
      
      // Focus input
      document.getElementById('messageInput').focus();
    }
  } catch (error) {
    console.error('Error creating conversation:', error);
    alert('Lỗi khi tạo cuộc trò chuyện mới. Vui lòng thử lại.');
  }
}

async function loadConversation(conversationId) {
  if (!isLoggedIn) return;
  
  try {
    const response = await fetch(`api/conversations.php?action=get&id=${conversationId}`);
    const data = await response.json();
    
    if (data.success) {
      currentConversationId = conversationId;
      
      // Clear current chat
      const chatBody = document.getElementById('chatBody');
      chatBody.innerHTML = '';
      
      // Load messages
      data.messages.forEach(msg => {
        if (msg.message) {
          addMessage(msg.message, 'user');
        }
        if (msg.response) {
          addMessage(msg.response, 'bot');
        }
      });
      
      // Update sidebar
      renderConversationHistory();
      
      // Close sidebar on mobile
      closeSidebar();
      
      // Focus input
      document.getElementById('messageInput').focus();
    }
  } catch (error) {
    console.error('Error loading conversation:', error);
  }
}

async function saveMessageToConversation(message, response) {
  if (!isLoggedIn || !currentConversationId) return;
  
  try {
    const saveResponse = await fetch('api/conversations.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'add_message',
        conversation_id: currentConversationId,
        message: message,
        response: response
      })
    });
    
    const data = await saveResponse.json();
    
    if (data.success) {
      // Reload conversation history to update titles and dates
      await loadConversationHistory();
    }
  } catch (error) {
    console.error('Error saving message:', error);
  }
}

async function deleteConversation(conversationId) {
  if (!confirm('Bạn có chắc chắn muốn xóa cuộc trò chuyện này?')) return;
  
  try {
    const response = await fetch('api/conversations.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: conversationId })
    });
    
    const data = await response.json();
    
    if (data.success) {
      // If deleting current conversation, create new one
      if (conversationId === currentConversationId) {
        currentConversationId = null;
        const chatBody = document.getElementById('chatBody');
        chatBody.innerHTML = `
          <div class="text-center text-muted p-4">
            <h4>🎉 Bắt đầu cuộc trò chuyện!</h4>
            <p>Gửi tin nhắn để bắt đầu cuộc trò chuyện mới.</p>
          </div>
        `;
      }
      
      // Reload conversation history
      await loadConversationHistory();
    }
  } catch (error) {
    console.error('Error deleting conversation:', error);
    alert('Lỗi khi xóa cuộc trò chuyện. Vui lòng thử lại.');
  }
}

// Sidebar functionality
function toggleSidebar() {
  const sidebar = document.getElementById('chatSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const chatLayout = document.getElementById('chatLayout');
  const isOpen = sidebar.classList.contains('active');
  
  if (isOpen) {
    closeSidebar();
  } else {
    openSidebar();
  }
}

function openSidebar() {
  const sidebar = document.getElementById('chatSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const chatLayout = document.getElementById('chatLayout');
  
  sidebar.classList.add('active');
  overlay.classList.add('active');
  
  // On desktop, push content. On mobile, overlay
  if (window.innerWidth > 768) {
    chatLayout.classList.add('sidebar-open');
  } else {
    document.body.style.overflow = 'hidden';
  }
}

function closeSidebar() {
  const sidebar = document.getElementById('chatSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const chatLayout = document.getElementById('chatLayout');
  
  sidebar.classList.remove('active');
  overlay.classList.remove('active');
  chatLayout.classList.remove('sidebar-open');
  document.body.style.overflow = '';
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  // Sidebar toggle
  document.getElementById('sidebarToggle').addEventListener('click', toggleSidebar);
  document.getElementById('sidebarOverlay').addEventListener('click', closeSidebar);
  
  // Focus message input
  document.getElementById('messageInput').focus();
  
  // ESC key to close sidebar
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeSidebar();
    }
  });
  
  // Load conversation history if logged in
  if (isLoggedIn) {
    loadConversationHistory();
  }
});

// Make functions available globally
window.sendMessage = sendMessage;
window.toggleSidebar = toggleSidebar;
window.showLoginPrompt = showLoginPrompt;
window.createNewConversation = createNewConversation;
window.loadConversation = loadConversation;
window.deleteConversation = deleteConversation;
</script>

</body>
</html>