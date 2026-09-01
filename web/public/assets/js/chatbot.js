document.addEventListener('DOMContentLoaded', function() {
    const chatBtn = document.getElementById('chatbot-btn');
    const chatWindow = document.getElementById('chatbot-window');
    const chatClose = document.getElementById('chatbot-close');
    const chatInput = document.getElementById('chatbot-input');
    const chatSend = document.getElementById('chatbot-send');
    const chatMessages = document.getElementById('chatbot-messages');
    const typingIndicator = document.getElementById('chatbot-typing');
    const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';

    if (!chatBtn || !chatWindow) return;

    chatBtn.addEventListener('click', () => {
        chatWindow.style.display = chatWindow.style.display === 'flex' ? 'none' : 'flex';
        if(chatWindow.style.display === 'flex') {
            chatInput.focus();
            scrollToBottom();
        }
    });

    chatClose.addEventListener('click', () => {
        chatWindow.style.display = 'none';
    });

    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    chatSend.addEventListener('click', sendMessage);

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function appendMessage(sender, text, isError = false) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-msg ${sender} ${isError ? 'error' : ''}`;
        
        // Parse simple markdown if bot
        if (sender === 'bot' && !isError) {
            // Simple line breaks
            let formattedText = text.replace(/\n/g, '<br>');
            // Bold
            formattedText = formattedText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            msgDiv.innerHTML = formattedText;
        } else {
            msgDiv.textContent = text;
        }
        
        chatMessages.appendChild(msgDiv);
        scrollToBottom();
    }

    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        // Add user message
        appendMessage('user', message);
        chatInput.value = '';
        chatInput.disabled = true;
        chatSend.disabled = true;

        // Show typing
        typingIndicator.style.display = 'flex';
        chatMessages.appendChild(typingIndicator); // move to bottom
        scrollToBottom();

        // Send to backend
        fetch(`${baseUrl}/chatbot`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 401) throw new Error('Silakan login terlebih dahulu.');
                throw new Error('Terjadi kesalahan pada server.');
            }
            return response.json();
        })
        .then(data => {
            typingIndicator.style.display = 'none';
            if (data.error) {
                appendMessage('bot', data.error, true);
            } else {
                appendMessage('bot', data.response);
            }
        })
        .catch(error => {
            typingIndicator.style.display = 'none';
            appendMessage('bot', 'Gagal menghubungi AI Chatbot. ' + error.message, true);
        })
        .finally(() => {
            chatInput.disabled = false;
            chatSend.disabled = false;
            chatInput.focus();
        });
    }
});
