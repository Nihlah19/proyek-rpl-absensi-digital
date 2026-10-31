<?php
/**
 * AppSetting.php
 * Model untuk pengelolaan pengaturan sistem dinamis (Database & .env synchronization).
 */
class AppSetting extends Model {
    private static $cache = [];
    private static $tableChecked = false;

    public function __construct() {
        parent::__construct();
        self::ensureTable();
    }

    /**
     * Memastikan tabel app_settings selalu tersedia
     */
    public static function ensureTable() {
        if (self::$tableChecked) return;
        try {
            $db = new Database();
            $db->query("CREATE TABLE IF NOT EXISTS app_settings (
                setting_key VARCHAR(50) PRIMARY KEY,
                setting_value TEXT NULL,
                description VARCHAR(255) NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            $db->execute();
            self::$tableChecked = true;
        } catch (Exception $e) {
            error_log('[AppSetting] Gagal inisialisasi tabel: ' . $e->getMessage());
        }
    }

    /**
     * Ambil nilai pengaturan berdasarkan key (dengan fallback default)
     */
    public static function get($key, $default = null) {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        self::ensureTable();
        try {
            $db = new Database();
            $db->query("SELECT setting_value FROM app_settings WHERE setting_key = :k LIMIT 1");
            $db->bind(':k', $key);
            $row = $db->single();

            if ($row && $row->setting_value !== null && $row->setting_value !== '') {
                self::$cache[$key] = $row->setting_value;
                return $row->setting_value;
            }
        } catch (Exception $e) {
            error_log('[AppSetting] Error get ' . $key . ': ' . $e->getMessage());
        }

        self::$cache[$key] = $default;
        return $default;
    }

    /**
     * Simpan nilai pengaturan
     */
    public static function set($key, $value, $description = null) {
        self::ensureTable();
        try {
            $db = new Database();
            $db->query("INSERT INTO app_settings (setting_key, setting_value, description) 
                        VALUES (:k, :v, :d) 
                        ON DUPLICATE KEY UPDATE setting_value = :v2, description = COALESCE(:d2, description)");
            $db->bind(':k', $key);
            $db->bind(':v', $value);
            $db->bind(':d', $description);
            $db->bind(':v2', $value);
            $db->bind(':d2', $description);
            $res = $db->execute();

            self::$cache[$key] = $value;
            return $res;
        } catch (Exception $e) {
            error_log('[AppSetting] Error set ' . $key . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sinkronisasi file .env agar tidak terjadi desinkronisasi saat reload environment
     */
    public static function updateEnvFile($key, $value) {
        $envPaths = [
            dirname(dirname(dirname(__DIR__))) . '/.env',
            __DIR__ . '/../../.env',
            __DIR__ . '/../../../.env'
        ];

        $envFile = null;
        foreach ($envPaths as $p) {
            if (file_exists($p)) {
                $envFile = $p;
                break;
            }
        }

        if (!$envFile || !is_writable($envFile)) {
            return false;
        }

        $content = file_get_contents($envFile);
        $pattern = '/^' . preg_quote($key, '/') . '\s*=.*$/m';
        $escapedVal = addcslashes($value, '"\\');
        $newLine = $key . '="' . $escapedVal . '"';

        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, $newLine, $content);
        } else {
            $newContent = rtrim($content) . PHP_EOL . $newLine . PHP_EOL;
        }

        $res = @file_put_contents($envFile, $newContent);
        if ($res !== false) {
            putenv(sprintf('%s=%s', $key, $value));
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            return true;
        }
        return false;
    }

    /**
     * Ambil konfigurasi AI saat ini
     */
    public static function getAiConfig() {
        $dbKey = self::get('gemini_api_key');
        $envKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : ($_ENV['GEMINI_API_KEY'] ?? '');
        $activeKey = !empty($dbKey) ? $dbKey : $envKey;

        $activeModel = self::get('gemini_model', 'gemini-2.5-flash');

        $maskedKey = '';
        if (!empty($activeKey)) {
            $len = strlen($activeKey);
            if ($len > 12) {
                $maskedKey = substr($activeKey, 0, 7) . '...' . substr($activeKey, -5);
            } else {
                $maskedKey = substr($activeKey, 0, 3) . '***';
            }
        }

        return [
            'api_key'      => $activeKey,
            'masked_key'   => $maskedKey,
            'model'        => $activeModel,
            'is_configured'=> !empty($activeKey),
            'updated_at'   => self::get('gemini_updated_at', null)
        ];
    }

    /**
     * Simpan konfigurasi API key & model pilihan
     */
    public static function saveAiConfig($apiKey, $model) {
        $apiKey = trim($apiKey);
        $model  = trim($model);

        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'API Key Gemini tidak boleh kosong.'];
        }

        if (empty($model)) {
            $model = 'gemini-2.5-flash';
        }

        self::set('gemini_api_key', $apiKey, 'Google Gemini AI Chatbot API Key');
        self::set('gemini_model', $model, 'Model AI yang aktif digunakan chatbot');
        self::set('gemini_updated_at', date('Y-m-d H:i:s'), 'Waktu terakhir konfigurasi diubah');

        // Sinkronisasi ke .env
        self::updateEnvFile('GEMINI_API_KEY', $apiKey);

        return [
            'success' => true,
            'message' => 'Konfigurasi AI Chatbot (API Key & Model) berhasil diperbarui!',
            'data'    => self::getAiConfig()
        ];
    }

    /**
     * Mengambil daftar model dari Google Gemini API secara dinamis berdasarkan API key yang diberikan
     */
    public static function fetchGeminiModels($apiKey = null) {
        if (empty($apiKey)) {
            $conf = self::getAiConfig();
            $apiKey = $conf['api_key'];
        }

        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'API Key belum diisi. Masukkan API Key terlebih dahulu.',
                'models'  => self::getDefaultModels()
            ];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode($apiKey);
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 6
        ]);

        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'message' => 'Koneksi gagal: ' . $curlError,
                'models'  => self::getDefaultModels()
            ];
        }

        $data = json_decode($resp, true);

        if ($httpCode !== 200 || !isset($data['models'])) {
            $errMessage = $data['error']['message'] ?? ('HTTP Error ' . $httpCode);
            return [
                'success' => false,
                'message' => 'Gagal memverifikasi API Key ke Google: ' . $errMessage,
                'models'  => self::getDefaultModels()
            ];
        }

        $cleanModels = [];
        $recommendedMap = [
            'gemini-2.5-flash'      => ['label' => 'Rekomendasi Utama', 'badge' => 'Cepat, Cerdas & Hemat', 'priority' => 1],
            'gemini-2.5-flash-lite' => ['label' => 'Super Ringan', 'badge' => 'Paling Hemat Token', 'priority' => 2],
            'gemini-3.8-flash'      => ['label' => 'Generasi 3.8', 'badge' => 'Fitur & Model Terkini', 'priority' => 3],
            'gemini-2.5-pro'        => ['label' => 'Pro Intelligence', 'badge' => 'Penalaran Kompleks', 'priority' => 4],
            'gemini-flash-latest'   => ['label' => 'Flash Latest', 'badge' => 'Versi Flash Terkini Otomatis', 'priority' => 5],
            'gemini-flash-lite-latest' => ['label' => 'Lite Latest', 'badge' => 'Versi Ringan Terkini', 'priority' => 6],
            'gemini-pro-latest'     => ['label' => 'Pro Latest', 'badge' => 'Versi Pro Terkini Otomatis', 'priority' => 7],
            'gemini-3.7-flash'      => ['label' => 'Gemini 3.7 Flash', 'badge' => 'Performa Tinggi', 'priority' => 8],
            'gemini-3.5-flash'      => ['label' => 'Gemini 3.5 Flash', 'badge' => 'Stabil', 'priority' => 9],
            'gemma-4-31b-it'        => ['label' => 'Gemma 4 31B', 'badge' => 'Open Model Google', 'priority' => 10],
        ];

        foreach ($data['models'] as $m) {
            $methods = $m['supportedGenerationMethods'] ?? [];
            if (!in_array('generateContent', $methods)) {
                continue;
            }

            $modelId = str_replace('models/', '', $m['name']);

            // Saring model yang bukan untuk percakapan teks standar
            if (preg_match('/(tts|transcribe|clip|robotics|banana|image-preview|image$|computer-use|deep-research)/i', $modelId)) {
                continue;
            }

            $recInfo = $recommendedMap[$modelId] ?? null;

            $cleanModels[] = [
                'id'          => $modelId,
                'name'        => $m['displayName'] ?? $modelId,
                'description' => $m['description'] ?? '',
                'input_limit' => $m['inputTokenLimit'] ?? null,
                'output_limit'=> $m['outputTokenLimit'] ?? null,
                'is_recommended' => ($recInfo !== null),
                'badge'       => $recInfo['badge'] ?? null,
                'priority'    => $recInfo['priority'] ?? 99,
            ];
        }

        // Urutkan: prioritas teratas dulu, lalu urut abjad nama
        usort($cleanModels, function($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return strcmp($a['id'], $b['id']);
            }
            return $a['priority'] <=> $b['priority'];
        });

        return [
            'success' => true,
            'message' => 'Berhasil mengambil ' . count($cleanModels) . ' model AI yang didukung API Key ini.',
            'models'  => $cleanModels
        ];
    }

    /**
     * Melakukan test koneksi & uji prompt ke model yang dipilih
     */
    public static function testGeminiPing($apiKey, $model) {
        $apiKey = trim($apiKey);
        $model  = trim($model);

        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'API Key tidak boleh kosong.'];
        }
        if (empty($model)) {
            $model = 'gemini-2.5-flash';
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);
        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => 'Halo! Mohon jawab dalam 1 kalimat pendek: API key valid dan siap digunakan pada sistem absensi.']]]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 120,
                'temperature'     => 0.2
            ]
        ];

        $startTime = microtime(true);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        $latencyMs = round((microtime(true) - $startTime) * 1000);

        if ($curlError) {
            return [
                'success' => false,
                'message' => 'Gagal menghubungi server Google Gemini: ' . $curlError,
                'latency' => $latencyMs
            ];
        }

        $data = json_decode($resp, true);

        if ($httpCode === 200 && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $reply = trim($data['candidates'][0]['content']['parts'][0]['text']);
            return [
                'success' => true,
                'message' => 'Koneksi Berhasil!',
                'reply'   => $reply,
                'latency' => $latencyMs,
                'model'   => $model
            ];
        }

        $errMsg = $data['error']['message'] ?? ('HTTP Error ' . $httpCode);
        return [
            'success' => false,
            'message' => 'Gagal mendapatkan respon dari model ' . $model . ': ' . $errMsg,
            'latency' => $latencyMs
        ];
    }

    /**
     * Daftar model default standar jika belum/gagal terhubung ke remote list
     */
    public static function getDefaultModels() {
        return [
            [
                'id' => 'gemini-2.5-flash',
                'name' => 'Gemini 2.5 Flash',
                'description' => 'Model rekomendasi utama: cepat, cerdas, dan efisien untuk percakapan asisten absensi.',
                'badge' => 'Rekomendasi Utama',
                'is_recommended' => true,
                'priority' => 1
            ],
            [
                'id' => 'gemini-2.5-flash-lite',
                'name' => 'Gemini 2.5 Flash-Lite',
                'description' => 'Model ultra ringan dengan konsumsi token terhemat.',
                'badge' => 'Super Ringan',
                'is_recommended' => true,
                'priority' => 2
            ],
            [
                'id' => 'gemini-3.8-flash',
                'name' => 'Gemini 3.8 Flash',
                'description' => 'Model generasi 3.8 dengan kapabilitas dan akurasi tinggi.',
                'badge' => 'Generasi Terkini',
                'is_recommended' => true,
                'priority' => 3
            ],
            [
                'id' => 'gemini-2.5-pro',
                'name' => 'Gemini 2.5 Pro',
                'description' => 'Model penalaran mendalam untuk analisis data dan pertanyaan kompleks.',
                'badge' => 'Pro Intelligence',
                'is_recommended' => true,
                'priority' => 4
            ],
            [
                'id' => 'gemini-flash-latest',
                'name' => 'Gemini Flash Latest',
                'description' => 'Otomatis menggunakan pembaruan model flash paling baru dari Google.',
                'badge' => 'Versi Terbaru',
                'is_recommended' => true,
                'priority' => 5
            ]
        ];
    }
}
