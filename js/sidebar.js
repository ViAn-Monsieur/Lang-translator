// Chat History Sidebar Module
// Created: 2025-07-05

class ChatSidebar {
    constructor() {
        this.isOpen = false;
        this.conversations = [];
        this.currentConversationId = null;
        this.API_PATH = window.location.pathname.includes('/admin/')
            ? '../api/conversations_fixed.php'
            : 'api/conversations_fixed.php';
        this.init();
    }

    init() {
        this.createSidebarElements();
        this.bindEvents();
        this.loadConversations();
    }

    createSidebarElements() {
        // Create sidebar overlay
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        overlay.id = 'sidebarOverlay';
        document.body.appendChild(overlay);

        // Create sidebar toggle button
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'sidebar-toggle';
        toggleBtn.id = 'sidebarToggle';
        toggleBtn.innerHTML = '☰';
        toggleBtn.title = 'Lịch sử cuộc trò chuyện';
        document.body.appendChild(toggleBtn);

        // Create sidebar
        const sidebar = document.createElement('div');
        sidebar.className = 'sidebar';
        sidebar.id = 'chatSidebar';
        sidebar.innerHTML = this.getSidebarHTML();
        document.body.appendChild(sidebar);
    }

    getSidebarHTML() {
        return `
            <div class="sidebar-header">
                <h3>💬 Lịch sử Chat</h3>
                <button class="btn btn-sm btn-primary" onclick="chatSidebar.createNewConversation()">
                    ➕ Mới
                </button>
            </div>
            <div class="sidebar-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchConversations" 
                           placeholder="🔍 Tìm kiếm cuộc trò chuyện...">
                </div>
                <div id="conversationsList">
                    <div class="text-center text-muted p-3">
                        Đang tải...
                    </div>
                </div>
            </div>
        `;
    }

    bindEvents() {
        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            this.toggle();
        });

        // Close sidebar when clicking overlay
        document.getElementById('sidebarOverlay').addEventListener('click', () => {
            this.close();
        });

        // Search conversations
        document.addEventListener('input', (e) => {
            if (e.target.id === 'searchConversations') {
                this.searchConversations(e.target.value);
            }
        });

        // Handle ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.isOpen = true;
        document.getElementById('chatSidebar').classList.add('active');
        document.getElementById('sidebarOverlay').classList.add('active');
        document.getElementById('sidebarToggle').classList.add('sidebar-active');
        document.body.style.overflow = 'hidden';
        
        // Focus search input
        setTimeout(() => {
            const searchInput = document.getElementById('searchConversations');
            if (searchInput) searchInput.focus();
        }, 300);
    }

    close() {
        this.isOpen = false;
        document.getElementById('chatSidebar').classList.remove('active');
        document.getElementById('sidebarOverlay').classList.remove('active');
        document.getElementById('sidebarToggle').classList.remove('sidebar-active');
        document.body.style.overflow = '';
    }

    async loadConversations() {
        try {
            const response = await fetch(this.API_PATH);
            
            const data = await response.json();
            
            if (data.success) {
                this.conversations = data.conversations;
                this.renderConversations();
            } else {
                this.showError(data.message || 'Không thể tải lịch sử cuộc trò chuyện');
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
            this.showError('Lỗi kết nối. Vui lòng thử lại.');
        }
    }

    renderConversations(conversations = this.conversations) {
        const container = document.getElementById('conversationsList');
        
        if (!conversations || conversations.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted p-3">
                    <p>📝 Chưa có cuộc trò chuyện nào</p>
                    <button class="btn btn-sm btn-outline-primary" onclick="chatSidebar.createNewConversation()">
                        Bắt đầu trò chuyện đầu tiên
                    </button>
                </div>
            `;
            return;
        }

        const html = conversations.map(conv => this.getConversationItemHTML(conv)).join('');
        container.innerHTML = html;
    }

    getConversationItemHTML(conversation) {
        const isActive = this.currentConversationId === conversation.id;
        const truncatedTitle = conversation.title.length > 30 
            ? conversation.title.substring(0, 30) + '...' 
            : conversation.title;
            
        const timeAgo = this.getTimeAgo(conversation.updated_at);
        
        return `
            <div class="sidebar-item ${isActive ? 'active' : ''}" 
                 onclick="chatSidebar.selectConversation(${conversation.id})"
                 data-conversation-id="${conversation.id}">
                <div class="flex-grow-1">
                    <div class="conversation-title" title="${conversation.title}">
                        ${truncatedTitle}
                    </div>
                    <div class="conversation-meta text-muted" style="font-size: 0.75rem;">
                        💬 ${conversation.message_count} • ${timeAgo}
                    </div>
                </div>
                <div class="conversation-actions">
                    <button class="btn btn-sm" onclick="event.stopPropagation(); chatSidebar.renameConversation(${conversation.id})" 
                            title="Đổi tên">
                        ✏️
                    </button>
                    <button class="btn btn-sm text-danger" onclick="event.stopPropagation(); chatSidebar.deleteConversation(${conversation.id})" 
                            title="Xóa">
                        🗑️
                    </button>
                </div>
            </div>
        `;
    }

    searchConversations(query) {
        if (!query.trim()) {
            this.renderConversations();
            return;
        }

        const filtered = this.conversations.filter(conv => 
            conv.title.toLowerCase().includes(query.toLowerCase())
        );
        
        this.renderConversations(filtered);
    }

    async createNewConversation() {
        try {
            const response = await fetch(this.API_PATH, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'create',
                    title: 'Cuộc trò chuyện mới'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Reload conversations and select new one
                await this.loadConversations();
                this.selectConversation(data.conversation_id);
                this.close();
                
                // Clear chat interface
                this.clearChatInterface();
                
                this.showSuccess('Đã tạo cuộc trò chuyện mới');
            } else {
                this.showError(data.message || 'Không thể tạo cuộc trò chuyện mới');
            }
        } catch (error) {
            console.error('Error creating conversation:', error);
            this.showError('Lỗi kết nối. Vui lòng thử lại.');
        }
    }

    async selectConversation(conversationId) {
        try {
            // Update UI
            this.currentConversationId = conversationId;
            
            // Update active state
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.classList.remove('active');
            });
            
            const selectedItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
            if (selectedItem) {
                selectedItem.classList.add('active');
            }
            
            // Load conversation messages
            const response = await fetch(`${this.API_PATH}?action=get&id=${conversationId}`);
            const data = await response.json();
            
            if (data.success) {
                this.loadConversationMessages(data.messages);
                this.close();
            } else {
                this.showError(data.message || 'Không thể tải cuộc trò chuyện');
            }
        } catch (error) {
            console.error('Error selecting conversation:', error);
            this.showError('Lỗi kết nối. Vui lòng thử lại.');
        }
    }

    loadConversationMessages(messages) {
        const chatBody = document.getElementById('chatBody');
        if (!chatBody) return;
        
        // Clear current messages
        chatBody.innerHTML = '';
        
        // Add messages
        messages.forEach(msg => {
            this.addMessageToChat(msg.message, 'user');
            if (msg.response) {
                this.addMessageToChat(msg.response, 'bot');
            }
        });
        
        // Scroll to bottom
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    addMessageToChat(text, sender) {
        const chatBody = document.getElementById('chatBody');
        if (!chatBody) return;
        
        const msg = document.createElement('div');
        msg.className = `message ${sender}`;
        msg.textContent = text;
        chatBody.appendChild(msg);
    }

    clearChatInterface() {
        const chatBody = document.getElementById('chatBody');
        if (chatBody) {
            chatBody.innerHTML = '';
        }
        
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            messageInput.value = '';
        }
    }

    async renameConversation(conversationId) {
        const currentConv = this.conversations.find(c => c.id === conversationId);
        if (!currentConv) return;
        
        const newTitle = prompt('Nhập tên mới cho cuộc trò chuyện:', currentConv.title);
        if (!newTitle || newTitle.trim() === currentConv.title) return;
        
        try {
            const response = await fetch(this.API_PATH, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'rename',
                    id: conversationId,
                    title: newTitle.trim()
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                await this.loadConversations();
                this.showSuccess('Đã đổi tên cuộc trò chuyện');
            } else {
                this.showError(data.message || 'Không thể đổi tên cuộc trò chuyện');
            }
        } catch (error) {
            console.error('Error renaming conversation:', error);
            this.showError('Lỗi kết nối. Vui lòng thử lại.');
        }
    }

    async deleteConversation(conversationId) {
        if (!confirm('Bạn có chắc chắn muốn xóa cuộc trò chuyện này?')) {
            return;
        }
        
        try {
            const response = await fetch(this.API_PATH, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'delete',
                    id: conversationId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // If deleting current conversation, clear chat
                if (this.currentConversationId === conversationId) {
                    this.currentConversationId = null;
                    this.clearChatInterface();
                }
                
                await this.loadConversations();
                this.showSuccess('Đã xóa cuộc trò chuyện');
            } else {
                this.showError(data.message || 'Không thể xóa cuộc trò chuyện');
            }
        } catch (error) {
            console.error('Error deleting conversation:', error);
            this.showError('Lỗi kết nối. Vui lòng thử lại.');
        }
    }

    async saveMessage(message, response) {
        if (!this.currentConversationId) {
            // Create new conversation if none selected
            await this.createNewConversation();
        }
        
        try {
            const saveResponse = await fetch(this.API_PATH, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'add_message',
                    conversation_id: this.currentConversationId,
                    message: message,
                    response: response
                })
            });
            
            const data = await saveResponse.json();
            
            if (data.success) {
                // Update conversation list to reflect new message count
                await this.loadConversations();
            }
        } catch (error) {
            console.error('Error saving message:', error);
        }
    }

    getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Vừa xong';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} phút trước`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} giờ trước`;
        if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} ngày trước`;
        
        return date.toLocaleDateString('vi-VN');
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type = 'info') {
        // Simple notification system
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
            color: white;
            border-radius: 4px;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideInRight 0.3s ease reverse';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Public method to integrate with existing chat functionality
    onMessageSent(message, response) {
        this.saveMessage(message, response);
    }
}

// Initialize sidebar when DOM is loaded
let chatSidebar;
document.addEventListener('DOMContentLoaded', function() {
    chatSidebar = new ChatSidebar();
});

// Make it available globally for integration
window.chatSidebar = chatSidebar;