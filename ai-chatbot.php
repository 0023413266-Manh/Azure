</script>

<!-- Nút mở khung chat -->
<!-- Nút mở khung chat -->
<!-- Nút mở khung chat -->
<button id="ai-chat-btn" onclick="toggleChatWindow()" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; background: #0078d4; color: white; border: none; border-radius: 50px; padding: 12px 20px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
    💬 AI Tư Vấn Thời Trang
</button>

<!-- Khung Chatbot -->
<div id="ai-chat-box" style="display: none; position: fixed; bottom: 80px; right: 20px; z-index: 9999; width: 360px; height: 500px; background: white; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); flex-direction: column; overflow: hidden; font-family: Arial, sans-serif;">
    <div style="background: #0078d4; color: white; padding: 12px 15px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
        <span>🤖 AI Fashion Advisor</span>
        <div>
            <span onclick="clearChatHistory()" title="Xoá lịch sử chat" style="cursor: pointer; font-size: 16px; margin-right: 12px;">🗑️</span>
            <span onclick="toggleChatWindow()" style="cursor: pointer; font-size: 18px;">✖</span>
        </div>
    </div>

    <div id="chat-messages" style="flex: 1; padding: 15px; overflow-y: auto; background: #f9f9f9; font-size: 14px; line-height: 1.5;">
        <!-- Lịch sử tin nhắn sẽ được load tự động tại đây -->
    </div>

    <div style="padding: 10px; background: white; border-top: 1px solid #ddd; display: flex;">
        <input type="text" id="chat-input" placeholder="Hỏi AI tư vấn phối đồ..." style="flex: 1; padding: 8px 12px; border: 1px solid #ccc; border-radius: 20px; outline: none;" onkeypress="if(event.key === 'Enter') sendUserMessage()">
        <button onclick="sendUserMessage()" style="margin-left: 8px; background: #0078d4; color: white; border: none; padding: 8px 15px; border-radius: 20px; cursor: pointer;">Gửi</button>
    </div>
</div>

<script>
// 1. Quản lý Mảng Lịch sử Chat trong localStorage
let chatHistory = JSON.parse(localStorage.getItem('timeless_ai_chat_history')) || [];

// Khi mở trang, load lại toàn bộ cuộc trò chuyện cũ
document.addEventListener('DOMContentLoaded', function() {
    renderChatHistory();
});

// 2. Ẩn/Hiện khung Chat
function toggleChatWindow() {
    const box = document.getElementById('ai-chat-box');
    box.style.display = (box.style.display === 'none' || box.style.display === '') ? 'flex' : 'none';
}

// 3. Hàm Xoá Lịch Sử Chat
function clearChatHistory() {
    if (confirm("Bạn có muốn xoá toàn bộ lịch sử trò chuyện với AI không?")) {
        chatHistory = [];
        localStorage.removeItem('timeless_ai_chat_history');
        renderChatHistory();
    }
}

// 4. Biến thẻ [ANH:] và [NUT:] thành HTML
function formatAiResponse(text) {
    if (!text) return '';

    let formatted = text;

    formatted = formatted.replace(/###\s*(.*?)(?:\n|<br>|$)/g, '<b style="color: #0078d4; font-size: 15px; display: block; margin-top: 8px;">$1</b>');
    formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');

    formatted = formatted.replace(/\[ANH:\s*([^\]]+)\]/gi, function(match, src) {
        let cleanSrc = src.trim();
        return '<div style="margin: 10px 0;">' +
               '<img src="' + cleanSrc + '" alt="Sản phẩm" style="max-width: 100%; height: auto; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.15); display: block;" ' +
               'onerror="this.onerror=null; this.src=\'https://via.placeholder.com/300x200?text=Hình+Ảnh+Đồng+Hồ\';">' +
               '</div>';
    });

    formatted = formatted.replace(/\[NUT:\s*([^\|]+)\|\s*([^\]]+)\]/gi, function(match, label, url) {
        let cleanLabel = label.trim();
        let cleanUrl = url.trim();
        return '<div style="margin: 8px 0;">' +
               '<a href="' + cleanUrl + '" target="_blank" style="display: inline-block; padding: 8px 14px; background-color: #0078d4; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 13px; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">' + cleanLabel + '</a>' +
               '</div>';
    });

    formatted = formatted.replace(/\n/g, '<br>');

    return formatted;
}

// 5. Hàm Hiển thị Lịch sử Tin nhắn ra Giao diện
function renderChatHistory() {
    const msgBox = document.getElementById('chat-messages');
    if (!msgBox) return;

    if (chatHistory.length === 0) {
        msgBox.innerHTML = `
            <div style="margin-bottom: 10px; background: #e9ecef; padding: 8px 12px; border-radius: 8px; max-width: 85%;">
                Xin chào! Bạn muốn tư vấn phối đồng hồ với trang phục gì hôm nay?
            </div>`;
        return;
    }

    let html = '';
    chatHistory.forEach(msg => {
        if (msg.role === 'user') {
            const safeText = msg.content.replace(/</g, "&lt;").replace(/>/g, "&gt;");
            html += `<div style="margin-bottom: 10px; text-align: right;"><span style="background: #0078d4; color: white; padding: 8px 12px; border-radius: 8px; display: inline-block; max-width: 85%;">${safeText}</span></div>`;
        } else {
            let formattedReply = formatAiResponse(msg.content);
            html += `<div style="margin-bottom: 10px;"><span style="background: #e9ecef; color: #333; padding: 10px 14px; border-radius: 8px; display: inline-block; max-width: 85%;">${formattedReply}</span></div>`;
        }
    });

    msgBox.innerHTML = html;
    msgBox.scrollTop = msgBox.scrollHeight;
}

// 6. Hàm Gửi Tin Nhắn
// 6. Hàm Gửi Tin Nhắn (Đã sửa tự động tìm đường dẫn gốc)
async function sendUserMessage() {
    const input = document.getElementById('chat-input');
    const msgBox = document.getElementById('chat-messages');
    if (!input || !msgBox) return;

    const text = input.value.trim();
    if (!text) return;

    // Lưu tin nhắn của User vào mảng lịch sử
    chatHistory.push({ role: 'user', content: text });
    localStorage.setItem('timeless_ai_chat_history', JSON.stringify(chatHistory));

    input.value = '';
    renderChatHistory();

    // Thông báo chờ
    const loadingId = 'loading-' + Date.now();
    msgBox.innerHTML += `<div id="${loadingId}" style="margin-bottom: 10px;"><span style="background: #e9ecef; padding: 8px 12px; border-radius: 8px; display: inline-block;">⏳ AI đang phân tích...</span></div>`;
    msgBox.scrollTop = msgBox.scrollHeight;

    // 🎯 TỰ ĐỘNG TÌM ĐƯỜNG DẪN ĐÚNG ĐẾN CHAT-API.PHP
    // Nếu đang đứng trong thư mục con chi_tiet_sp/ thì đi ra ngoài bằng '../chat-api.php'
    let apiUrl = 'chat-api.php';
    if (window.location.pathname.includes('/chi_tiet_sp/')) {
        apiUrl = '../chat-api.php';
    }

    try {
        const response = await fetch(apiUrl, { // <--- Sử dụng apiUrl động ở đây
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ history: chatHistory })
        });
        
        const data = await response.json();
        const loadingEl = document.getElementById(loadingId);
        if (loadingEl) loadingEl.remove();

        if (data.reply) {
            chatHistory.push({ role: 'assistant', content: data.reply });
            localStorage.setItem('timeless_ai_chat_history', JSON.stringify(chatHistory));
            renderChatHistory();
        } else {
            msgBox.innerHTML += `<div style="margin-bottom: 10px;"><span style="background: #f8d7da; color: #721c24; padding: 8px 12px; border-radius: 8px; display: inline-block;">❌ Lỗi không nhận được câu trả lời.</span></div>`;
        }
    } catch (error) {
        console.error("Chat Error:", error);
        const loadingEl = document.getElementById(loadingId);
        if (loadingEl) loadingEl.remove();
        msgBox.innerHTML += `<div style="margin-bottom: 10px;"><span style="background: #f8d7da; color: #721c24; padding: 8px 12px; border-radius: 8px; display: inline-block;">❌ Lỗi đọc dữ liệu từ server.</span></div>`;
    }
    msgBox.scrollTop = msgBox.scrollHeight;
}
</script>
