<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $db_name = DB_NAME;

    private $dbh;
    private $stmt;

    public function __construct() {
        $portStr = (defined('DB_PORT') && DB_PORT) ? ';port=' . DB_PORT : '';
        $dsn = 'mysql:host=' . $this->host . $portStr . ';dbname=' . $this->db_name . ';charset=utf8mb4';
        
        $isVercel = isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL']) || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'vercel.app') !== false);

        $option = [
            PDO::ATTR_PERSISTENT => !$isVercel, // Non-persistent pada serverless Vercel
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+07:00'"
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $option);
            $this->dbh->exec("SET time_zone = '+07:00'");
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            
            if ($isVercel) {
                $this->renderVercelDbNotice($e->getMessage());
                exit;
            }
            
            die("Terjadi kesalahan pada koneksi database. Silakan hubungi administrator.");
        }
    }

    private function renderVercelDbNotice($errorMsg) {
        http_response_code(200); // 200 agar browser tidak menampilkan halaman error default Vercel
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Vercel Deployment — Konfigurasi Database Diperlukan</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Inter', sans-serif; }
                code, pre { font-family: 'JetBrains Mono', monospace; }
            </style>
        </head>
        <body class="bg-[#f7f9f5] text-[#1c1c1c] min-h-screen flex items-center justify-center p-4">
            <div class="max-w-2xl w-full bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-[#163300] px-8 py-6 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-[#9fe870] flex items-center justify-center text-[#163300] font-black text-lg">
                            <i class="fa-solid fa-server"></i>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold">SMK NU Wargabinangun</h1>
                            <p class="text-xs text-[#9fe870]">Sistem Presensi Siswa, Guru & PKL Terpadu</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-[#9fe870]/20 text-[#9fe870] text-xs font-semibold rounded-full border border-[#9fe870]/30 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#9fe870] animate-pulse"></span>
                        Vercel PHP Live
                    </span>
                </div>

                <!-- Body -->
                <div class="p-8 space-y-6">
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-amber-50 border border-amber-200">
                        <i class="fa-solid fa-circle-exclamation text-amber-600 text-xl mt-0.5"></i>
                        <div>
                            <h2 class="font-bold text-amber-900 text-sm">Aplikasi Web Berhasil Berjalan, Menunggu Konfigurasi Database Cloud</h2>
                            <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                                Kode PHP dan antarmuka web telah sukses dieksekusi di Vercel. Karena Vercel merupakan platform serverless, database MySQL harus menggunakan database cloud remote (seperti TiDB Cloud, Aiven MySQL, atau Railway).
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Langkah Menghubungkan Database:</h3>
                        <ol class="text-xs text-gray-700 space-y-2 list-decimal list-inside bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <li>Buka <strong>Vercel Dashboard</strong> &rarr; Pilih project <strong>absensmknu</strong>.</li>
                            <li>Masuk ke menu <strong>Settings</strong> &rarr; <strong>Environment Variables</strong>.</li>
                            <li>Tambahkan variabel database MySQL cloud berikut:</li>
                        </ol>

                        <div class="overflow-x-auto border border-gray-200 rounded-2xl">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-100/70 text-gray-600 font-semibold border-b">
                                    <tr>
                                        <th class="p-2.5">Key</th>
                                        <th class="p-2.5">Contoh Value</th>
                                        <th class="p-2.5">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-gray-700 font-mono">
                                    <tr>
                                        <td class="p-2.5 text-blue-600 font-bold">DB_HOST</td>
                                        <td class="p-2.5">gateway01.ap-southeast-1.prod.aws.tidbcloud.com</td>
                                        <td class="p-2.5 font-sans">Host MySQL Cloud</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2.5 text-blue-600 font-bold">DB_PORT</td>
                                        <td class="p-2.5">4000 (atau 3306)</td>
                                        <td class="p-2.5 font-sans">Port MySQL</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2.5 text-blue-600 font-bold">DB_USER</td>
                                        <td class="p-2.5">xxxxxx.root</td>
                                        <td class="p-2.5 font-sans">Username DB</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2.5 text-blue-600 font-bold">DB_PASS</td>
                                        <td class="p-2.5">password_anda</td>
                                        <td class="p-2.5 font-sans">Password DB</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2.5 text-blue-600 font-bold">DB_NAME</td>
                                        <td class="p-2.5">absensi_rfid</td>
                                        <td class="p-2.5 font-sans">Nama Database</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Technical Detail Collapsible -->
                    <details class="text-xs border border-gray-200 rounded-2xl p-3 bg-gray-50/50">
                        <summary class="cursor-pointer font-medium text-gray-600 select-none flex items-center justify-between">
                            <span><i class="fa-solid fa-code text-gray-400 mr-1.5"></i> Detail Teknis Percobaan Koneksi</span>
                            <span class="text-gray-400 text-[10px]">Klik untuk melihat</span>
                        </summary>
                        <div class="mt-3 p-3 bg-gray-900 text-green-400 rounded-xl overflow-x-auto">
                            <code><?= htmlspecialchars($errorMsg); ?></code>
                            <div class="text-gray-400 text-[10px] mt-2">Target Host: <?= htmlspecialchars($this->host); ?>:<?= htmlspecialchars(defined('DB_PORT') ? DB_PORT : '3306'); ?></div>
                        </div>
                    </details>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        <button onclick="window.location.reload()" class="flex-1 py-3 px-4 bg-[#163300] hover:bg-[#054d28] text-[#9fe870] font-bold rounded-2xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                            <i class="fa-solid fa-rotate-right"></i>
                            Cek Koneksi Ulang
                        </button>
                        <a href="/?preview=1" class="flex-1 py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-2xl text-xs flex items-center justify-center gap-2 transition">
                            <i class="fa-solid fa-eye"></i>
                            Lihat Halaman Landing (Preview)
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-8 py-3 border-t border-gray-100 text-center text-[11px] text-gray-400">
                    Sistem Presensi Siswa & PKL &bull; SMK NU Wargabinangun &bull; Vercel Cloud Edition
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    public function query($query) {
        $this->stmt = $this->dbh->prepare($query);
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $logFile = dirname(__DIR__) . '/storage/logs/error.log';
            $msg = '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . PHP_EOL;
            @file_put_contents($logFile, $msg, FILE_APPEND);
            throw $e;
        }
    }

    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}
