<?php
class ChatbotController extends Controller {
    public function index() {
        // Hanya menerima POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        // Cek login
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');

        if (empty($message)) {
            echo json_encode(['error' => 'Message is required']);
            exit;
        }

        // Batasi panjang pesan (cegah abuse)
        if (strlen($message) > 1000) {
            $message = substr($message, 0, 1000);
        }

        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role'] ?? 'user';

        // Panggil API Gemini
        $geminiResponse = $this->callGeminiApi($message, $userRole);

        // Simpan ke database
        $chatHistory = $this->model('ChatHistory');
        $chatHistory->addHistory($userId, $message, $geminiResponse);

        echo json_encode([
            'response' => $geminiResponse
        ]);
        exit;
    }

    private function callGeminiApi($message, $role) {
        $apiKey = GEMINI_API_KEY;
        if (empty($apiKey)) {
            return "Sistem chatbot belum dikonfigurasi. Hubungi administrator.";
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $apiKey;

        // Context prompt agar chatbot fokus pada sistem absensi RFID dan role
        $systemPrompt = "Anda adalah asisten AI untuk Sistem Absensi RFID Sekolah. ";
        $systemPrompt .= "Pengguna ini memiliki peran sebagai: " . $role . ". ";
        $systemPrompt .= "Gunakan Bahasa Indonesia. Fokus pada sistem absensi, jangan mengarang fitur yang tidak ada, dan berikan langkah troubleshooting berdasarkan dokumentasi project. ";
        $systemPrompt .= "Jangan mengklaim melakukan tindakan di luar kemampuan sistem (misalnya mengubah database langsung). ";
        $systemPrompt .= "Jika pertanyaan berkaitan dengan RFID atau ESP32, berikan troubleshooting terkait perangkat keras atau jaringan (misal cek koneksi, cek power, restart ESP32, barcode/RFID belum didaftarkan).";

        $data = [
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [
                        ["text" => $systemPrompt . "\n\nPertanyaan pengguna: " . $message]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        // Hanya disable SSL verify di development
        if (defined('APP_ENV') && APP_ENV === 'development') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $responseData = json_decode($response, true);
            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                return $responseData['candidates'][0]['content']['parts'][0]['text'];
            }
        }

        return "Maaf, sistem AI sedang mengalami gangguan. Silakan coba lagi nanti.";
    }
}
