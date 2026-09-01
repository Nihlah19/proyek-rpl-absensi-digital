<?php
class TelegramHelper {
    public static function sendMessage($chat_id, $message) {
        // Retrieve bot token from environment or config
        $bot_token = getenv('TELEGRAM_BOT_TOKEN'); // Assuming it's in .env
        
        if (!$bot_token || empty($chat_id)) {
            return false;
        }

        $url = "https://api.telegram.org/bot" . $bot_token . "/sendMessage";
        $data = [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
