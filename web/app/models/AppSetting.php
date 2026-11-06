<?php
/**
 * AppSetting.php
 * Model untuk pengelolaan pengaturan sistem dinamis (Database & .env synchronization).
 * Mendukung AI Multi-Provider: Groq, xAI Grok, dan Google Gemini.
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
     * Simpan / perbarui pengaturan
     */
    public static function set($key, $value, $description = null) {
        self::ensureTable();
        try {
            $db = new Database();
            $db->query("INSERT INTO app_settings (setting_key, setting_value, description)
                        VALUES (:k, :v, :d)
                        ON DUPLICATE KEY UPDATE setting_value = :v2, description = COALESCE(:d2, description), updated_at = NOW()");
            $db->bind(':k', $key);
            $db->bind(':v', $value);
            $db->bind(':d', $description);
            $db->bind(':v2', $value);
            $db->bind(':d2', $description);
            $db->execute();

            self::$cache[$key] = $value;
            return true;
        } catch (Exception $e) {
            error_log('[AppSetting] Error set ' . $key . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update file .env secara aman
     */
    public static function updateEnvFile($key, $value) {
        $envPath = dirname(__DIR__, 2) . '/.env';
        if (!file_exists($envPath)) {
            @file_put_contents($envPath, "{$key}=\"{$value}\"\n");
            return;
        }

        $content = file_get_contents($envPath);
        $pattern = "/^{$key}=.*/m";
        $escapedVal = str_replace('"', '\"', $value);

        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, "{$key}=\"{$escapedVal}\"", $content);
        } else {
            $newContent = rtrim($content) . "\n{$key}=\"{$escapedVal}\"\n";
        }

        @file_put_contents($envPath, $newContent);
    }

    /**
     * Deteksi Provider AI secara otomatis berdasarkan format API Key
     */
    public static function detectAiProvider($apiKey) {
        $key = trim($apiKey);
        if (strpos($key, 'gsk_') === 0) {
            return 'groq';
        } elseif (strpos($key, 'xai-') === 0) {
            return 'grok';
        } elseif (strpos($key, 'AIza') === 0) {
            return 'gemini';
        } elseif (strpos($key, 'sk-') === 0) {
            return 'groq';
        }
        return 'groq'; // Default modern inference engine
    }

    /**
     * Ambil konfigurasi AI saat ini (Multi-Provider: Groq / Grok / Gemini)
     */
    public static function getAiConfig() {
        $dbKey = self::get('ai_api_key') ?: self::get('gemini_api_key');
        $envKey = getenv('AI_API_KEY') ?: (getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? ''));
        $activeKey = !empty($dbKey) ? $dbKey : $envKey;

        $dbProvider = self::get('ai_provider');
        $provider = !empty($dbProvider) ? $dbProvider : self::detectAiProvider($activeKey);

        $defaultModel = match($provider) {
            'groq'   => 'llama-3.3-70b-versatile',
            'grok'   => 'grok-2-latest',
            'gemini' => 'gemini-2.5-flash',
            default  => 'llama-3.3-70b-versatile'
        };

        $activeModel = self::get('ai_model') ?: self::get('gemini_model', $defaultModel);

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
            'provider'     => $provider,
            'api_key'      => $activeKey,
            'masked_key'   => $maskedKey,
            'model'        => $activeModel,
            'is_configured'=> !empty($activeKey),
            'updated_at'   => self::get('ai_updated_at') ?: self::get('gemini_updated_at', null)
        ];
    }

    /**
     * Simpan konfigurasi API key, model pilihan, dan provider
     */
    public static function saveAiConfig($apiKey, $model, $provider = null) {
        $apiKey = trim($apiKey);
        $model  = trim($model);

        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'API Key AI tidak boleh kosong.'];
        }

        if (empty($provider) || $provider === 'auto') {
            $provider = self::detectAiProvider($apiKey);
        }

        if (empty($model)) {
            $model = match($provider) {
                'groq'   => 'llama-3.3-70b-versatile',
                'grok'   => 'grok-2-latest',
                'gemini' => 'gemini-2.5-flash',
                default  => 'llama-3.3-70b-versatile'
            };
        }

        self::set('ai_provider', $provider, 'Provider AI: groq, grok, gemini');
        self::set('ai_api_key', $apiKey, 'API Key AI Chatbot');
        self::set('ai_model', $model, 'Model AI aktif');
        self::set('ai_updated_at', date('Y-m-d H:i:s'), 'Waktu terakhir konfigurasi diubah');

        // Kompatibilitas mundur dengan Gemini
        self::set('gemini_api_key', $apiKey, 'AI API Key Backward Compatibility');
        self::set('gemini_model', $model, 'AI Model Backward Compatibility');

        // Sinkronisasi ke .env
        self::updateEnvFile('AI_API_KEY', $apiKey);
        self::updateEnvFile('GEMINI_API_KEY', $apiKey);

        return [
            'success' => true,
            'message' => 'Konfigurasi AI Chatbot (Provider: ' . strtoupper($provider) . ', Model: ' . $model . ') berhasil disimpan!',
            'data'    => self::getAiConfig()
        ];
    }

    /**
     * Mengambil daftar model secara dinamis menggunakan Python script atau cURL
     */
    public static function fetchAiModels($provider = null, $apiKey = null) {
        if (empty($apiKey)) {
            $conf = self::getAiConfig();
            $storedKey = $conf['api_key'] ?? '';
            $storedProvider = $conf['provider'] ?? 'gemini';

            if (!empty($provider) && $provider !== 'auto') {
                $keyProvider = self::detectAiProvider($storedKey);
                if ($keyProvider === $provider) {
                    $apiKey = $storedKey;
                } else {
                    $apiKey = '';
                }
            } else {
                $apiKey = $storedKey;
                $provider = $storedProvider;
            }
        }

        if (empty($provider) || $provider === 'auto') {
            $provider = self::detectAiProvider($apiKey);
        }

        // Jika API key masih kosong, sediakan model rekomendasi
        if (empty($apiKey)) {
            return [
                'success'  => true,
                'provider' => $provider,
                'message'  => 'Daftar model rekomendasi (' . strtoupper($provider) . '). Masukkan API Key Anda untuk memuat model kustom.',
                'models'   => self::getDefaultModels($provider)
            ];
        }

        // 1. Coba eksekusi melalui Python script ai_model_fetcher.py jika tersedia
        $possiblePaths = [
            dirname(__DIR__, 3) . '/scripts/ai_model_fetcher.py',
            dirname(__DIR__, 2) . '/scripts/ai_model_fetcher.py',
            dirname(__DIR__, 1) . '/scripts/ai_model_fetcher.py'
        ];
        $pythonScript = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $pythonScript = $path;
                break;
            }
        }

        if ($pythonScript) {
            $cmd = 'python ' . escapeshellarg($pythonScript) . ' --provider ' . escapeshellarg($provider) . ' --api-key ' . escapeshellarg($apiKey) . ' --json 2>&1';
            $output = @shell_exec($cmd);
            if (!empty($output)) {
                $json = json_decode($output, true);
                if ($json && isset($json['success']) && $json['success'] === true && !empty($json['models'])) {
                    return $json;
                }
            }
        }

        // 2. Fallback via PHP cURL langsung
        return self::fetchModelsViaCurl($provider, $apiKey);
    }

    /**
     * Pengambilan model langsung via cURL HTTP (Groq, Grok, Gemini)
     */
    private static function fetchModelsViaCurl($provider, $apiKey) {
        if (empty($apiKey)) {
            return [
                'success' => true,
                'provider'=> $provider,
                'message' => 'Daftar model standar (Kunci API belum diisi)',
                'models'  => self::getDefaultModels($provider)
            ];
        }

        if ($provider === 'groq') {
            $url = "https://api.groq.com/openai/v1/models";
            $headers = ["Authorization: Bearer " . trim($apiKey), "Content-Type: application/json"];
        } elseif ($provider === 'grok') {
            $url = "https://api.x.ai/v1/models";
            $headers = ["Authorization: Bearer " . trim($apiKey), "Content-Type: application/json"];
        } else {
            $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode(trim($apiKey));
            $headers = ["Content-Type: application/json"];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_CONNECTTIMEOUT => 6
        ]);

        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            return [
                'success' => true,
                'provider'=> $provider,
                'message' => 'Tidak dapat terhubung ke endpoint ' . strtoupper($provider) . ' (HTTP ' . $httpCode . '). Menggunakan daftar model rekomendasi.',
                'models'  => self::getDefaultModels($provider)
            ];
        }

        $data = json_decode($resp, true);
        $models = [];

        if ($provider === 'groq' || $provider === 'grok') {
            $raw = $data['data'] ?? [];
            foreach ($raw as $m) {
                $mid = $m['id'] ?? '';
                $isRec = in_array($mid, ['llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'grok-2-latest', 'grok-beta']);
                $models[] = [
                    'id'             => $mid,
                    'name'           => $mid,
                    'provider'       => $provider,
                    'is_recommended' => $isRec,
                    'badge'          => $isRec ? 'Rekomendasi' : null,
                    'context_window' => $m['context_window'] ?? '-'
                ];
            }
        } else {
            $raw = $data['models'] ?? [];
            foreach ($raw as $m) {
                $mid = str_replace('models/', '', $m['name'] ?? '');
                $methods = $m['supportedGenerationMethods'] ?? [];
                if (!in_array('generateContent', $methods)) continue;
                $isRec = in_array($mid, ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-1.5-flash']);
                $models[] = [
                    'id'             => $mid,
                    'name'           => $m['displayName'] ?? $mid,
                    'provider'       => 'gemini',
                    'is_recommended' => $isRec,
                    'badge'          => $isRec ? 'Rekomendasi' : null
                ];
            }
        }

        usort($models, function($a, $b) {
            return ($b['is_recommended'] ? 1 : 0) <=> ($a['is_recommended'] ? 1 : 0);
        });

        return [
            'success'      => true,
            'provider'     => $provider,
            'total_models' => count($models),
            'message'      => 'Berhasil mengambil ' . count($models) . ' model dari ' . strtoupper($provider),
            'models'       => !empty($models) ? $models : self::getDefaultModels($provider)
        ];
    }

    /**
     * Daftar model default standar per provider
     */
    public static function getDefaultModels($provider = 'groq') {
        if ($provider === 'grok') {
            return [
                ['id' => 'grok-2-latest', 'name' => 'xAI Grok 2 (Latest)', 'provider' => 'grok', 'badge' => 'Rekomendasi', 'is_recommended' => true, 'context_window' => '128k'],
                ['id' => 'grok-beta', 'name' => 'xAI Grok Beta', 'provider' => 'grok', 'badge' => 'Cepat', 'is_recommended' => true, 'context_window' => '128k']
            ];
        } elseif ($provider === 'gemini') {
            return [
                ['id' => 'gemini-2.5-flash', 'name' => 'Gemini 2.5 Flash', 'provider' => 'gemini', 'badge' => 'Rekomendasi Utama', 'is_recommended' => true],
                ['id' => 'gemini-2.0-flash', 'name' => 'Gemini 2.0 Flash', 'provider' => 'gemini', 'badge' => 'Cepat', 'is_recommended' => true],
                ['id' => 'gemini-1.5-flash', 'name' => 'Gemini 1.5 Flash', 'provider' => 'gemini', 'badge' => 'Stabil', 'is_recommended' => false]
            ];
        }

        // Default: Groq
        return [
            ['id' => 'llama-3.3-70b-versatile', 'name' => 'Llama 3.3 70B Versatile', 'provider' => 'groq', 'badge' => 'Rekomendasi Utama', 'is_recommended' => true, 'context_window' => '128k'],
            ['id' => 'llama-3.1-8b-instant', 'name' => 'Llama 3.1 8B Instant (Ultra Cepat)', 'provider' => 'groq', 'badge' => 'Paling Cepat', 'is_recommended' => true, 'context_window' => '128k'],
            ['id' => 'gemma2-9b-it', 'name' => 'Gemma 2 9B IT', 'provider' => 'groq', 'badge' => 'Ringan', 'is_recommended' => false, 'context_window' => '8k'],
            ['id' => 'mixtral-8x7b-32768', 'name' => 'Mixtral 8x7B 32k', 'provider' => 'groq', 'badge' => 'Stabil', 'is_recommended' => false, 'context_window' => '32k']
        ];
    }
}
