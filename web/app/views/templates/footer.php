        </main>
    </div>
</div>

<!-- Chatbot UI -->
<?php if(isset($_SESSION['user_id'])): ?>
<button id="chatbot-btn"><i class="fa fa-comments"></i></button>

<div id="chatbot-window">
    <div id="chatbot-header">
        <span>AI Assistant</span>
        <span id="chatbot-close">&times;</span>
    </div>
    <div id="chatbot-messages">
        <div class="chat-msg bot">Halo! Ada yang bisa saya bantu terkait sistem absensi?</div>
    </div>
    <div id="chatbot-typing" class="typing-indicator">
        <span></span><span></span><span></span>
    </div>
    <div id="chatbot-input-area">
        <input type="text" id="chatbot-input" placeholder="Ketik pesan..." autocomplete="off">
        <button id="chatbot-send"><i class="fa fa-paper-plane"></i></button>
    </div>
</div>
<script src="<?= BASEURL; ?>/assets/js/chatbot.js"></script>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= BASEURL; ?>/assets/js/script.js"></script>
</body>
</html>
