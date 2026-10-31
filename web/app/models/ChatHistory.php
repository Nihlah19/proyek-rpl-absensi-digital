<?php
class ChatHistory extends Model {
    public function addHistory($userId, $pesanUser, $balasanBot) {
        $this->db->query("INSERT INTO chat_history (user_id, pesan_user, balasan_bot) VALUES (:user_id, :pesan_user, :balasan_bot)");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':pesan_user', $pesanUser);
        $this->db->bind(':balasan_bot', $balasanBot);
        return $this->db->execute();
    }

    public function getHistory($userId, $limit = 10) {
        $this->db->query("SELECT * FROM chat_history WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function clearHistory($userId) {
        $this->db->query("DELETE FROM chat_history WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }
}
