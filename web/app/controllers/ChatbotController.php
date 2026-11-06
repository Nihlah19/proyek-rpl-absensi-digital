<?php
class ChatbotController extends Controller {

    // =========================================================
    // MAIN ENTRY POINT
    // =========================================================
    public function index() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $input   = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');

        if (empty($message)) {
            echo json_encode(['error' => 'Message is required']);
            exit;
        }

        if (strlen($message) > 1000) {
            $message = substr($message, 0, 1000);
        }

        $userId   = $_SESSION['user_id'];
        $rawRole  = $_SESSION['role'] ?? 'user';
        $userRole = in_array($rawRole, ['wali_kelas', 'walikelas']) ? 'wali_kelas' : strtolower($rawRole);

        // ── 1. Tolak permintaan manipulasi data ──────────────────
        $blocked = $this->detectDataManipulation($message);
        if ($blocked) {
            $botResponse = $blocked;
        } else {
            // ── 2. Tolak permintaan mengerjakan soal tugas sekolah / PR ──
            $homeworkBlock = $this->detectHomeworkRequest($message);
            if ($homeworkBlock) {
                $botResponse = $homeworkBlock;
            } else {
                // ── 3. Sapaan ringan & Pertanyaan Identitas (hemat kuota) ──
                $lowerMsg = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/u', '', $message)));
                $greetings = [
                    'halo','hallo','haloo','halooo','hai','haii','hi','hey','heyy','pagi','selamat pagi','siang','selamat siang',
                    'sore','selamat sore','malam','selamat malam','apa kabar','kabar','halo min','halo bot',
                    'assalamualaikum','assalammualaikum','assalamu\'alaikum','salam','ping','p','tes','test','oy','oi'
                ];
                $identityPhrases = [
                    'kamu siapa', 'siapa kamu', 'siapa anda', 'anda siapa', 'kamu siapa sih',
                    'siapa namamu', 'namamu siapa', 'siapa kamu ini', 'kamu itu siapa',
                    'kamu apa', 'apa kamu', 'kamu robot apa', 'kamu ai apa', 'siapa buat kamu',
                    'siapa yang buat kamu', 'siapa pembuatmu', 'fungsi kamu apa', 'apa fungsi kamu'
                ];
                $isIdentity = in_array($lowerMsg, $identityPhrases) ||
                              preg_match('/\b(kamu|anda|bot|ai)\s+(itu\s+)?siapa\b/i', $message) ||
                              preg_match('/\bsiapa\s+(kamu|anda|bot|ai)\b/i', $message) ||
                              preg_match('/\b(nama|identitas|fungsi)\s+(kamu|anda|bot)\b/i', $message);

                // Evaluasi hitungan matematika sederhana (misal: 1+1 berapa, 5*10, dsb)
                $mathClean = trim(preg_replace('/\s*(berapa|hasilnya|sama dengan|itu berapa|\?|=)/i', '', trim($message)));
                if (preg_match('~^(\d+(?:\.\d+)?)\s*([\+\-\*/xX:])\s*(\d+(?:\.\d+)?)$~i', $mathClean, $mMath)) {
                    $num1 = (float)$mMath[1];
                    $operator = strtolower($mMath[2]);
                    $num2 = (float)$mMath[3];
                    $calcRes = match($operator) {
                        '+' => $num1 + $num2,
                        '-' => $num1 - $num2,
                        '*', 'x' => $num1 * $num2,
                        '/', ':' => ($num2 != 0 ? round($num1 / $num2, 4) : 'tidak terdefinisi (pembagian dengan nol)'),
                        default => null
                    };
                    if ($calcRes !== null) {
                        $botResponse = "Hasil dari **{$mMath[1]} {$operator} {$mMath[3]}** adalah **{$calcRes}**.";
                    }
                } elseif (in_array($lowerMsg, $greetings)) {
                    $nama = $_SESSION['name'] ?? 'Pengguna';
                    $roleLabel = match($userRole) {
                        'siswa'      => 'Siswa',
                        'guru'       => 'Guru',
                        'wali_kelas' => 'Wali Kelas',
                        'kepsek'     => 'Kepala Sekolah',
                        'admin'      => 'Administrator',
                        default      => ucfirst($userRole),
                    };
                    $botResponse = "Halo, **{$nama}** ({$roleLabel}). Saya Asisten AI sistem absensi SMK NU Wargabinangun. Ada yang bisa saya bantu hari ini?";
                } elseif ($isIdentity) {
                    $botResponse = "Halo! Saya adalah **Asisten AI Resmi Sistem Absensi SMK NU Wargabinangun**.\n\n"
                                 . "Saya dapat membantu Anda untuk:\n"
                                 . "- Mengecek jadwal, presensi, dan rekap kehadiran sesuai hak akses peran akun Anda\n"
                                 . "- Panduan penggunaan sistem, solusi kendala scan kartu RFID & monitor QR guru\n"
                                 . "- Menjawab pertanyaan pengetahuan umum, teknologi, dan sains di luar database\n\n"
                                 . "(Catatan: Demi mendukung kemandirian belajar siswa, saya tidak melayani pengerjaan soal tugas sekolah atau PR.) Ada yang ingin Anda tanyakan?";
                } else {
                    // ── 4. Coba tangani secara lokal (DB query & RBAC) ──────
                    $localResponse = $this->handleLocalDatabaseQuery($message, $userId, $userRole);

                    if ($localResponse !== false) {
                        $botResponse = $localResponse;
                    } else {
                        // ── 5. Panggil Gemini API (Dengan RBAC Context Sync & General Knowledge) ──
                        $today      = date('Y-m-d');
                        $sessionKey = 'ai_chat_count_' . $today;
                        if (!isset($_SESSION[$sessionKey])) $_SESSION[$sessionKey] = 0;

                        if ($_SESSION[$sessionKey] >= 20) {
                            $botResponse = "Maaf, Anda telah mencapai batas harian pertanyaan AI. Silakan coba lagi besok.";
                        } else {
                            $botResponse = $this->callAiApi($message, $userRole, $userId);
                            if ($botResponse !== "Maaf, sistem AI sedang mengalami gangguan. Silakan coba lagi nanti.") {
                                $_SESSION[$sessionKey]++;
                            }
                        }
                    }
                }
            }
        }

        // Simpan histori
        $chatHistory = $this->model('ChatHistory');
        $chatHistory->addHistory($userId, $message, $botResponse);

        echo json_encode([
            'response'   => $botResponse,
            'quota_used' => $_SESSION['ai_chat_count_' . date('Y-m-d')] ?? 0,
            'quota_max'  => 20,
        ]);
        exit;
    }

    // =========================================================
    // CLEAR HISTORY
    // =========================================================
    public function clear_history() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $chatHistory = $this->model('ChatHistory');
        
        if (method_exists($chatHistory, 'clearHistory')) {
            $chatHistory->clearHistory($userId);
        }
        
        echo json_encode(['success' => true]);
        exit;
    }

    // =========================================================
    // DETEKSI & BLOKIR MANIPULASI DATABASE
    // =========================================================
    private function detectDataManipulation($message) {
        $lower = strtolower($message);

        // Kata kunci perintah berbahaya
        $dangerKeywords = [
            // SQL langsung
            'insert into','delete from','drop table','truncate','alter table',
            'update set','create table','grant ','revoke ',
            // Perintah bahasa alami untuk ubah data
            'tambah siswa','hapus siswa','tambahkan siswa','hapuskan siswa',
            'tambah guru','hapus guru','tambahkan guru','hapuskan guru',
            'ubah data','ganti data','edit data','update data',
            'tambah kelas','hapus kelas','buat kelas','pindah kelas',
            'daftarkan','daftar ulang','registrasi ulang',
            'reset password','ganti password','ubah password',
            'hapus absensi','ubah absensi','edit absensi',
            'tambah absen','input absen','catat absen',
            'insert','delete','drop','truncate',
        ];

        foreach ($dangerKeywords as $keyword) {
            if (strpos($lower, $keyword) !== false) {
                return "**Aksi Tidak Diizinkan**\n\nSaya hanya dapat **membaca dan menginformasikan** data dari sistem. Saya tidak memiliki akses untuk:\n- Menambah, menghapus, atau mengubah data siswa/guru\n- Memodifikasi absensi\n- Mengubah pengaturan sistem\n\nSilakan gunakan menu manajemen di sidebar untuk melakukan perubahan data resmi.";
            }
        }

        return false;
    }

    // =========================================================
    // DETEKSI & TOLAK PERMINTAAN TUGAS SEKOLAH / PR / CODING
    // =========================================================
    private function detectHomeworkRequest($message) {
        $lower = strtolower(trim($message));

        // 1. Deteksi Permintaan Pembuatan Kode Program / Coding / Skrip (Semua Role)
        $codePatterns = [
            // Segala penyebutan kata koding / coding / ngoding / codingan / kodingan
            '/\b(coding|koding|ngoding|codingan|kodingan)\b/i',

            // Bahasa pemrograman + konteks skrip/program/kode/syntax/looping/fungsi/dsb
            '/\b(python|java|c\+\+|cpp|c#|csharp|php|javascript|js|html|css|pascal|scratch|sql|golang|rust|ruby|dart|kotlin|swift)\b.*(script|skrip|program|kode|syntax|sintaks|loop|looping|perulangan|fungsi|def |class |array|variabel|algoritma|hitung|buat|bikin|tulis|tugas|soal|pr\b|latihan|contoh|rumus)/i',
            
            // Konteks skrip/program/kode/syntax/looping/dsb + bahasa pemrograman
            '/\b(script|skrip|program|kode|syntax|sintaks|source code|loop|looping|perulangan|fungsi|def |class |array|variabel|algoritma|soal|tugas|pr\b|latihan|buat|bikin|tulis|contoh)\b.*(python|java|c\+\+|cpp|c#|csharp|php|javascript|js|html|css|pascal|scratch|sql|golang|rust|ruby|dart|kotlin|swift)/i',

            // Permintaan membuat/menulis/mengerjakan program atau script secara umum
            '/\b(buat|buatkan|buatin|bikin|bikinkan|bikinin|tulis|tuliskan|tulisin|kerjakan|kerjain|contoh|minta|berikan|kasih|ajar|ajarin|ajarkan|bantu)\b.*(program|source code|kode program|script|skrip)/i',

            // Soal / tugas / PR pemrograman
            '/\b(soal|tugas|pr\b|pekerjaan rumah|latihan|ada soal)\b.*(pemrograman|program|script|skrip|python|java|c\+\+|pascal)/i',

            // Sebutan langsung python / c++ / pascal sebagai soal tugas atau latihan
            '/\b(soal|tugas|pr\b)\s+.*(python|java|c\+\+|cpp|pascal)/i',
            '/\b(python|c\+\+|cpp|pascal)\b/i',
        ];

        foreach ($codePatterns as $pat) {
            if (preg_match($pat, $lower)) {
                return "**Kebijakan Pembuatan Kode Program & Tugas Coding**\n\n"
                     . "Maaf, sebagai Asisten AI Sistem Absensi Sekolah, saya **tidak melayani pembuatan kode program, kodingan, skrip pemrograman, atau pengerjaan soal tugas coding (seperti Python, Java, C++, PHP, JavaScript, dsb) untuk peran apa pun (Siswa, Guru, Wali Kelas, Kepala Sekolah, maupun Admin)**.\n\n"
                     . "Kebijakan ini berlaku agar seluruh pengguna dan siswa dapat mengasah logika berpikir komputasional, algoritma, serta keterampilan memecahkan masalah dan menulis kode secara mandiri.\n\n"
                     . "**Bantuan yang tersedia:**\n"
                     . "1. Mengecek jadwal, presensi, dan rekap kehadiran sesuai hak akses peran Anda\n"
                     . "2. Solusi kendala teknis sistem absensi, scanner RFID, dan monitor QR guru\n"
                     . "3. Menjelaskan konsep teori umum atau cara kerja teknologi sekolah\n\n"
                     . "Silakan pelajari konsep dasarnya secara mandiri untuk pemahaman materi yang lebih baik.";
            }
        }

        // 2. Deteksi Permintaan Mengerjakan Tugas Sekolah / PR / Soal Ujian Umum
        $hwPatterns = [
            '/(kerjakan|selesaikan|jawabkan|jawab|bikinkan|buatkan|isi|isikan|bantu kerjakan)\s+.*(pr\b|tugas sekolah|soal\b|ujian|ulangan|pekerjaan rumah|latihan soal)/i',
            '/(jawaban|kunci jawaban)\s+.*(soal|nomor|tugas|pr\b|ujian|ulangan)/i',
            '/(tolong|bantu)\s+.*(kerjakan|jawab|selesaikan)\s+.*(soal|tugas|pr\b)/i',
            '/(tugas|pr)\s+(matematika|fisika|kimia|biologi|sejarah|bahasa|agama|ipa|ips|pkn|tik)\s+saya/i',
            '/\b(jawab|kerjakan)\s+soal\s+(nomor|no)?/i',
        ];

        foreach ($hwPatterns as $pat) {
            if (preg_match($pat, $lower)) {
                return "**Kebijakan Pengerjaan Tugas Sekolah & PR**\n\n"
                     . "Maaf, sebagai Asisten AI Sistem Absensi Sekolah, saya **tidak diperkenankan mengerjakan soal tugas sekolah, PR, atau ujian siswa secara langsung untuk peran apa pun (Siswa, Guru, Wali Kelas, Kepala Sekolah, maupun Admin)**.\n\n"
                     . "Hal ini bertujuan agar siswa dapat belajar mandiri, berpikir kritis, dan memahami materi pelajaran dengan baik.\n\n"
                     . "**Bantuan yang tersedia:**\n"
                     . "1. Mengecek jadwal, presensi, dan rekap kehadiran sesuai hak akses peran Anda\n"
                     . "2. Solusi kendala teknis sistem absensi, scanner RFID, dan monitor QR guru\n"
                     . "3. Menjelaskan konsep teori umum atau cara kerja teknologi sekolah\n\n"
                     . "Silakan pelajari konsep dasarnya secara mandiri untuk pemahaman materi pelajaran yang lebih maksimal.";
            }
        }

        return false;
    }

    // =========================================================
    // QUERY LOKAL KE DATABASE (DENGAN STRICT RBAC)
    // =========================================================
    private function handleLocalDatabaseQuery($message, $userId, $userRole) {
        $db    = new Database();
        $lower = strtolower($message);

        // Abaikan query lokal jika user bertanya penjelasan konsep, panduan, tutorial, alasan teknis, troubleshooting, atau pertanyaan umum
        if (preg_match('/(apa itu|pengertian|definisi|cara kerja|sejarah|kenapa|mengapa|apa bedanya|perbedaan|jelaskan|rekomendasi|tips|ceritakan|bagaimana cara|panduan|tutorial|langkah|bantuan|solusi|gagal|error|rusak|kendala)/i', $lower)) {
            // Pengecualian: jangan abaikan jika bertanya aktor/sesi login atau jadwal spesifik atau data presensi/izin
            if (!preg_match('/(aktor|sesi\s+login|role|peran|jadwal|kapan|mengajar|ngajar|jam|ronidah|guru|izin|sakit|absen|kehadiran|tanggal|tgl)/i', $lower)) {
                return false;
            }
        }

        // =====================================================================
        // 0.0 QUERY PRESENSI, IZIN, SAKIT, & REKAP BERDASARKAN TANGGAL (SEMUA PERAN DENGAN RBAC)
        // =====================================================================
        $dateAttendanceResponse = $this->handleDateAttendanceQuery($message, $userId, $userRole, $db);
        if ($dateAttendanceResponse !== false) {
            return $dateAttendanceResponse;
        }

        $today    = date('Y-m-d');
        $bulanIni = date('Y-m');
        $hariIni  = $this->hariIndonesia(date('l'));
        $besok    = $this->hariIndonesia(date('l', strtotime('+1 day')));

        // =====================================================================
        // 0. AKTOR & SESI LOGIN SISTEM (SEMUA PERAN / UMUM)
        // =====================================================================
        $isAktorQuery = preg_match('/(berapa\s+aktor|aktor.*sesi\s+login|sesi\s+login.*aktor|aktor.*sistem|siapa\s+saja\s+aktor|ada\s+berapa\s+aktor|role\s+apa\s+saja|ada\s+berapa\s+role|daftar\s+aktor|daftar\s+role|hak\s+akses\s+aktor|peran\s+pengguna|level\s+user|jenis\s+pengguna|sesi\s+login|berapa\s+role|ada\s+role\s+apa)/i', $lower) ||
                        (preg_match('/\b(aktor|role)\b/i', $lower) && preg_match('/(ada\s+berapa|berapa|siapa\s+saja|apa\s+saja|sebutkan|list|daftar|sistem|login)/i', $lower));

        if ($isAktorQuery) {
            return "**Aktor Sesi Login Sistem Absensi SMK NU Wargabinangun**\n\n"
                 . "Terdapat **5 aktor peran pengguna (roles)** dengan sesi login dan hak akses masing-masing:\n\n"
                 . "1. **Administrator (Admin)**\n"
                 . "   - Mengelola master data sekolah: siswa, guru, kelas, dan jadwal absensi.\n"
                 . "   - Manajemen kartu RFID (registrasi UID, ganti kartu, aktivasi/blokir).\n"
                 . "   - Monitoring & konfigurasi hardware scanner IoT RFID serta integrasi notifikasi Telegram.\n\n"
                 . "2. **Kepala Sekolah (Kepsek)**\n"
                 . "   - Monitoring eksekutif rekapitulasi kehadiran guru & siswa secara real-time.\n"
                 . "   - Persetujuan (approval) pengajuan surat izin/sakit/cuti dinas guru.\n"
                 . "   - Evaluasi kedisiplinan dan laporan tren keterlambatan sekolah.\n\n"
                 . "3. **Wali Kelas**\n"
                 . "   - Memantau absensi harian dan rekap kehadiran siswa khusus di kelas binaannya.\n"
                 . "   - Menyetujui atau menolak permohonan izin/sakit dan permohonan pulang cepat siswa binaan.\n"
                 . "   - Mengakses jadwal mengajar dan riwayat presensi mengajar pribadi sebagai guru.\n\n"
                 . "4. **Guru (Guru Mata Pelajaran)**\n"
                 . "   - Melakukan presensi masuk & pulang melalui monitor scan QR guru (barcode ID).\n"
                 . "   - Melihat jadwal mengajar mingguan dan riwayat kehadiran pribadi.\n"
                 . "   - Mengajukan surat izin dinas/pribadi kepada Kepala Sekolah.\n\n"
                 . "5. **Siswa**\n"
                 . "   - Melakukan tap kartu RFID pada scanner IoT gerbang sekolah saat datang dan pulang.\n"
                 . "   - Melihat status kartu RFID, jadwal sekolah, dan riwayat presensi pribadi.\n"
                 . "   - Mengajukan permohonan izin/sakit atau surat izin pulang cepat kepada Wali Kelas.";
        }

        // =====================================================================
        // 0.1 PENCARIAN JADWAL TERINTEGRASI: GURU & SISWA (SEMUA PERAN / DATABASE SYNC)
        // =====================================================================
        $isScheduleQuery = preg_match('/(jadwal|kapan|mengajar|ngajar|jam\s+(masuk|pulang|buka|datang|kbm|terlambat)|waktu\s+(masuk|pulang|kbm|mengajar)|hari apa|list hari|daftar hari)\s+.*?(guru|pengajar|siswa|murid|sekolah|kbm|pak|ibu|bu|bapak|ronidah|agus|andri|abu|abdul|didi|dimas|suwardi|iqbal|meli|an\'amu|fajrin|mulyono|saefudin|sobirin|subkhan|sumiyati|syamsul|trisa|badrun|uswatun)/i', $lower) ||
                           preg_match('/(kapan|jadwal|hari apa|jam berapa|dimulai\s+dari\s+jam|waktu\s+masuk\s+dan\s+pulang)\s+.*?(mengajar|ngajar|guru|siswa|sekolah|kbm)/i', $lower) ||
                           preg_match('/(jadwal\s+(guru|siswa|sekolah|kbm|mengajar|ngajar|masuk|pulang|absensi|pelajaran))/i', $lower) ||
                           preg_match('/\b(mengajar|ngajar)\b/i', $lower);

        if ($isScheduleQuery) {
            // Ambil daftar seluruh guru untuk matching nama
            $db->query("SELECT g.id as guru_id, g.nip, g.subject, u.name, k.nama_kelas as wali_kelas
                        FROM guru g 
                        JOIN users u ON u.id = g.user_id 
                        LEFT JOIN kelas k ON k.wali_kelas_id = u.id");
            $allGurus = $db->resultSet();

            $matchedGuru = null;
            $nameStopwords = ['guru', 'wali', 'kelas', 'absen', 'siswa', 'saya', 'kami', 'kita', 'buat', 'siapa', 'kapan', 'list', 'data', 'pada', 'dari', 'atau', 'akan', 'kehadiran', 'hari', 'jam', 'bisa', 'mengajar', 'ngajar', 'jadwal', 'waktu', 'sekolah', 'masuk', 'pulang', 'dan', 'ini', 'besok', 'berapa', 'sampai', 'dimulai'];

            foreach ($allGurus as $g) {
                $cleanName = preg_replace('/,\s*[\w\.\s]+$/i', '', $g->name);
                $cleanName = preg_replace('/\b(s\.?kom|s\.?pd|s\.?pd\.?i|s\.?t|amd\.?kom|m\.?pd|dr|drs|dra|se|s\.?ag)\b/i', '', $cleanName);
                $cleanName = trim($cleanName);

                if (stripos($lower, strtolower($cleanName)) !== false) {
                    $matchedGuru = $g;
                    break;
                }

                $words = preg_split('/[\s,\.]+/', strtolower($cleanName), -1, PREG_SPLIT_NO_EMPTY);
                foreach ($words as $w) {
                    if (strlen($w) >= 4 && !in_array($w, $nameStopwords) && stripos($lower, $w) !== false) {
                        $matchedGuru = $g;
                        break 2;
                    }
                }
            }

            // A. Jika guru spesifik ditemukan
            if ($matchedGuru) {
                $db->query("SELECT * FROM jadwal_guru WHERE guru_id = :gid ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')");
                $db->bind(':gid', $matchedGuru->guru_id);
                $allDays = $db->resultSet();

                $activeList = [];
                $liburList = [];
                foreach ($allDays as $d) {
                    if ($d->is_active == 1) {
                        $masuk  = substr($d->jam_datang, 0, 5);
                        $pulang = substr($d->jam_pulang, 0, 5);
                        $telat  = substr($d->batas_terlambat, 0, 5);
                        $tandaHariIni = ($d->hari === $hariIni) ? ' [Hari Ini]' : '';
                        $activeList[] = "- **{$d->hari}**{$tandaHariIni}: Masuk {$masuk} s/d Pulang {$pulang} WIB (Batas telat: {$telat})";
                    } else {
                        $liburList[] = $d->hari;
                    }
                }

                $waliInfo  = !empty($matchedGuru->wali_kelas) ? "\n- **Wali Kelas**: {$matchedGuru->wali_kelas}" : "";
                $mapelInfo = (!empty($matchedGuru->subject) && $matchedGuru->subject !== '-') ? "\n- **Mata Pelajaran**: {$matchedGuru->subject}" : "";
                $nipInfo   = (!empty($matchedGuru->nip) && $matchedGuru->nip !== '-') ? "\n- **NIP**: `{$matchedGuru->nip}`" : "";

                $respon = "**Jadwal Mengajar {$matchedGuru->name}**{$mapelInfo}{$nipInfo}{$waliInfo}\n\n";

                if (empty($activeList)) {
                    $respon .= "Saat ini belum ada jadwal mengajar aktif yang tercatat di sistem untuk Bapak/Ibu **{$matchedGuru->name}**.";
                } else {
                    $respon .= "**Hari & Jam Mengajar Aktif:**\n" . implode("\n", $activeList) . "\n\n";
                    if (!empty($liburList)) {
                        $respon .= "_Hari Libur / Tidak Mengajar: " . implode(', ', $liburList) . "._";
                    }
                }
                return $respon;
            }

            // B. Cek apakah menanyakan hari tertentu
            $hasTargetDay = preg_match('/(senin|selasa|rabu|kamis|jumat|sabtu|minggu|hari ini|besok)/i', $lower, $mDay);
            $targetHari = null;
            if ($hasTargetDay) {
                $targetRaw = strtolower($mDay[1]);
                $targetHari = match($targetRaw) {
                    'hari ini' => $hariIni,
                    'besok'    => $besok,
                    default    => ucfirst($targetRaw),
                };
            }

            $isTeacherFocus = preg_match('/\b(guru|pengajar|pendidik|ngajar|mengajar)\b/i', $lower);
            $isStudentFocus = preg_match('/\b(siswa|murid|pelajar|anak)\b/i', $lower);

            // B.1 JIKA ADA HARI TERTENTU
            if ($targetHari) {
                // Ambil data jadwal guru di hari tersebut
                $db->query("SELECT u.name, g.subject, j.jam_datang, j.jam_pulang, j.batas_terlambat 
                            FROM jadwal_guru j 
                            JOIN guru g ON g.id = j.guru_id 
                            JOIN users u ON u.id = g.user_id 
                            WHERE j.hari = :h AND j.is_active = 1 
                            ORDER BY j.jam_datang ASC");
                $db->bind(':h', $targetHari);
                $dayGurus = $db->resultSet();

                // Ambil data jadwal siswa di hari tersebut dari tabel jadwal_siswa
                $db->query("SELECT * FROM jadwal_siswa WHERE hari = :h");
                $db->bind(':h', $targetHari);
                $jsRow = $db->single();

                $minMasukGuru  = null;
                $maxPulangGuru = null;
                $minTelatGuru  = null;
                foreach ($dayGurus as $dg) {
                    $m = substr($dg->jam_datang, 0, 5);
                    $p = substr($dg->jam_pulang, 0, 5);
                    $t = substr($dg->batas_terlambat, 0, 5);
                    if ($minMasukGuru === null || $m < $minMasukGuru) $minMasukGuru = $m;
                    if ($maxPulangGuru === null || $p > $maxPulangGuru) $maxPulangGuru = $p;
                    if ($minTelatGuru === null || $t < $minTelatGuru) $minTelatGuru = $t;
                }

                // KASUS B.1.1: FOKUS GURU (misal: "waktu masuk dan pulang jadwal guru dimulai dari jam berapa sampai berapa di hari senin")
                if ($isTeacherFocus || (!$isStudentFocus && preg_match('/(guru|mengajar|ngajar)/i', $lower))) {
                    if (empty($dayGurus)) {
                        $respon = "**Jadwal Mengajar Guru Hari {$targetHari}**\n\n";
                        $respon .= "Tidak ada guru yang memiliki jadwal mengajar pada hari **{$targetHari}** (Libur / Tidak ada KBM guru).\n";
                        if ($jsRow && $jsRow->status_aktif) {
                            $respon .= "\n_Catatan: Jadwal Siswa tetap aktif dari pukul " . substr($jsRow->jam_datang, 0, 5) . " s/d " . substr($jsRow->jam_pulang, 0, 5) . " WIB._";
                        }
                        return $respon;
                    }

                    $totG = count($dayGurus);
                    $respon = "**Jadwal Mengajar Guru Hari {$targetHari}**\n\n";
                    $respon .= "Waktu masuk dan pulang jadwal guru pada hari **{$targetHari}** dimulai dari pukul **{$minMasukGuru} WIB** (jam masuk paling awal) sampai dengan pukul **{$maxPulangGuru} WIB** (jam pulang paling akhir).\n\n";
                    $respon .= "- **Jam Masuk Paling Awal** : {$minMasukGuru} WIB\n";
                    if ($minTelatGuru) {
                        $respon .= "- **Batas Toleransi Terlambat (Sesi Awal)**: {$minTelatGuru} WIB\n";
                    }
                    $respon .= "- **Jam Pulang Paling Akhir**: {$maxPulangGuru} WIB\n";
                    $respon .= "- **Total Guru Mengajar** : {$totG} Guru\n\n";

                    // Sinkronisasi dengan Jadwal Siswa dari database
                    if ($jsRow && $jsRow->status_aktif) {
                        $jsMasuk  = substr($jsRow->jam_datang, 0, 5);
                        $jsTelat  = substr($jsRow->batas_terlambat, 0, 5);
                        $jsPulang = substr($jsRow->jam_pulang, 0, 5);
                        $jsMulai  = substr($jsRow->jam_mulai_absensi, 0, 5);
                        $respon .= "**Sinkronisasi Jadwal Siswa (Hari {$targetHari}):**\n";
                        $respon .= "- Jam Masuk Siswa : {$jsMasuk} WIB (Batas telat: {$jsTelat} WIB, buka absen RFID: {$jsMulai} WIB)\n";
                        $respon .= "- Jam Pulang Siswa: {$jsPulang} WIB\n\n";
                    } elseif ($jsRow && !$jsRow->status_aktif) {
                        $respon .= "**Sinkronisasi Jadwal Siswa (Hari {$targetHari}):** Libur / KBM siswa tidak aktif.\n\n";
                    }

                    $respon .= "**Daftar Guru Mengajar Hari {$targetHari} (Total: {$totG} Guru):**\n";
                    foreach ($dayGurus as $i => $dg) {
                        $no = $i + 1;
                        $mapel = (!empty($dg->subject) && $dg->subject !== '-') ? " ({$dg->subject})" : "";
                        $masuk = substr($dg->jam_datang, 0, 5);
                        $pulang = substr($dg->jam_pulang, 0, 5);
                        $respon .= "{$no}. **{$dg->name}**{$mapel} — {$masuk} s/d {$pulang} WIB\n";
                    }
                    return $respon;
                }

                // KASUS B.1.2: FOKUS SISWA (misal: "jadwal siswa hari senin", "jam masuk siswa hari senin")
                if ($isStudentFocus && !$isTeacherFocus) {
                    if (!$jsRow || !$jsRow->status_aktif) {
                        return "**Jadwal Siswa Hari {$targetHari}**\n\nPada hari **{$targetHari}**, jadwal KBM / absensi siswa **Libur** (tidak aktif).";
                    }

                    $jsMasuk  = substr($jsRow->jam_datang, 0, 5);
                    $jsTelat  = substr($jsRow->batas_terlambat, 0, 5);
                    $jsPulang = substr($jsRow->jam_pulang, 0, 5);
                    $jsMulai  = substr($jsRow->jam_mulai_absensi, 0, 5);

                    $respon = "**Jadwal Siswa Hari {$targetHari}**\n\n";
                    $respon .= "Waktu masuk dan pulang siswa pada hari **{$targetHari}** adalah pukul **{$jsMasuk} WIB** s/d **{$jsPulang} WIB**.\n\n";
                    $respon .= "- **Mulai Buka Absen RFID** : {$jsMulai} WIB\n";
                    $respon .= "- **Jam Masuk Resmi**        : {$jsMasuk} WIB\n";
                    $respon .= "- **Batas Toleransi Telat**  : {$jsTelat} WIB\n";
                    $respon .= "- **Jam Pulang Siswa**       : {$jsPulang} WIB\n\n";

                    // Sinkronisasi dengan Jadwal Guru
                    if (!empty($dayGurus)) {
                        $totG = count($dayGurus);
                        $respon .= "**Sinkronisasi Jadwal Mengajar Guru (Hari {$targetHari}):**\n";
                        $respon .= "- Rentang Waktu KBM Guru : {$minMasukGuru} s/d {$maxPulangGuru} WIB\n";
                        $respon .= "- Total Guru Mengajar    : {$totG} Guru aktif\n\n";
                        $respon .= "_Ketik `jadwal guru hari {$targetHari}` untuk melihat rincian nama seluruh guru yang mengajar._";
                    }
                    return $respon;
                }

                // KASUS B.1.3: JADWAL SEKOLAH TERINTEGRASI HARI TERTENTU (misal: "jadwal hari senin", "jadwal sekolah hari senin")
                $respon = "**Jadwal Terintegrasi Sekolah Hari {$targetHari}**\n\n";
                if ($jsRow && $jsRow->status_aktif) {
                    $jsMasuk  = substr($jsRow->jam_datang, 0, 5);
                    $jsTelat  = substr($jsRow->batas_terlambat, 0, 5);
                    $jsPulang = substr($jsRow->jam_pulang, 0, 5);
                    $jsMulai  = substr($jsRow->jam_mulai_absensi, 0, 5);
                    $respon .= "1. **Jadwal Siswa (Gerbang Absensi RFID):**\n";
                    $respon .= "   - Jam Masuk  : {$jsMasuk} WIB (Batas telat: {$jsTelat} WIB, Buka: {$jsMulai} WIB)\n";
                    $respon .= "   - Jam Pulang : {$jsPulang} WIB\n\n";
                } else {
                    $respon .= "1. **Jadwal Siswa:** Libur / Tidak ada KBM.\n\n";
                }

                if (!empty($dayGurus)) {
                    $totG = count($dayGurus);
                    $respon .= "2. **Jadwal Guru (Kegiatan Belajar Mengajar):**\n";
                    $respon .= "   - Rentang Waktu KBM : {$minMasukGuru} s/d {$maxPulangGuru} WIB\n";
                    $respon .= "   - Batas Telat Sesi 1: {$minTelatGuru} WIB\n";
                    $respon .= "   - Guru Mengajar     : {$totG} Guru aktif\n\n";
                    $respon .= "_Data tersinkronisasi langsung antara tabel `jadwal_siswa` dan `jadwal_guru` di database sekolah._\n\n";
                    $respon .= "Ketik `jadwal guru hari {$targetHari}` untuk melihat daftar lengkap guru yang mengajar.";
                } else {
                    $respon .= "2. **Jadwal Guru:** Tidak ada jadwal mengajar aktif.\n";
                }

                return $respon;
            }

            // B.2 JIKA TIDAK ADA HARI SPESIFIK (JADWAL MINGGUAN / REKAP)
            if ($isTeacherFocus && !$isStudentFocus) {
                $db->query("SELECT hari, MIN(jam_datang) as min_masuk, MAX(jam_pulang) as max_pulang, COUNT(DISTINCT guru_id) as total_guru 
                            FROM jadwal_guru 
                            WHERE is_active = 1 
                            GROUP BY hari 
                            ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");
                $weeklyGuru = $db->resultSet();

                if (empty($weeklyGuru)) {
                    return "**Jadwal Mengajar Guru**\n\nBelum ada jadwal mengajar guru yang aktif di sistem.";
                }

                $respon = "**Rekap Jadwal Mengajar Guru (Mingguan)**\n\n";
                foreach ($weeklyGuru as $wg) {
                    $tandaHariIni = ($wg->hari === $hariIni) ? ' [Hari Ini]' : '';
                    $masuk  = substr($wg->min_masuk, 0, 5);
                    $pulang = substr($wg->max_pulang, 0, 5);
                    $respon .= "- **{$wg->hari}**{$tandaHariIni}: {$masuk} s/d {$pulang} WIB ({$wg->total_guru} Guru)\n";
                }
                $respon .= "\n_Untuk melihat daftar guru pada hari tertentu, ketik misalnya: `jadwal guru hari senin`._";
                return $respon;
            }

            // B.3 JADWAL SEKOLAH TERINTEGRASI MINGGUAN
            $db->query("SELECT * FROM jadwal_siswa ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')");
            $jadwalSiswaList = $db->resultSet();

            $db->query("SELECT hari, MIN(jam_datang) as min_masuk, MAX(jam_pulang) as max_pulang, COUNT(DISTINCT guru_id) as total_guru 
                        FROM jadwal_guru 
                        WHERE is_active = 1 
                        GROUP BY hari");
            $guruGrouped = [];
            foreach ($db->resultSet() as $gg) {
                $guruGrouped[$gg->hari] = $gg;
            }

            $respon = "**Jadwal Sekolah Terintegrasi (Siswa & Guru)**\n\n";
            foreach ($jadwalSiswaList as $js) {
                $tanda = ($js->hari === $hariIni) ? ' [Hari Ini]' : '';
                if ($js->status_aktif) {
                    $sMasuk  = substr($js->jam_datang, 0, 5);
                    $sTelat  = substr($js->batas_terlambat, 0, 5);
                    $sPulang = substr($js->jam_pulang, 0, 5);
                    
                    $gInfo = "";
                    if (isset($guruGrouped[$js->hari])) {
                        $gRow = $guruGrouped[$js->hari];
                        $gMasuk  = substr($gRow->min_masuk, 0, 5);
                        $gPulang = substr($gRow->max_pulang, 0, 5);
                        $gInfo = " | Guru: {$gMasuk}-{$gPulang} ({$gRow->total_guru} Guru)";
                    }

                    $respon .= "- **{$js->hari}**{$tanda}: Siswa Masuk {$sMasuk} (Telat {$sTelat}) - Pulang {$sPulang}{$gInfo}\n";
                } else {
                    $respon .= "- **{$js->hari}**{$tanda}: Libur\n";
                }
            }
            $respon .= "\n_Ketik `jadwal guru hari [nama hari]` atau `jadwal siswa hari [nama hari]` untuk rincian harian._";
            return $respon;
        }

        // =====================================================================
        // FILTER PEMBATASAN AKSES BERDASARKAN ROLE (EARLY RBAC ENFORCEMENT)
        // =====================================================================

        // 1. SISWA mencoba mengakses data privat guru (absen, kehadiran, nip, barcode, kontak, rekap)
        if ($userRole === 'siswa' && preg_match('/(absen|kehadiran|nip|barcode|kontak|nomor|profil|rekap)\s+(guru|pengajar|wali|bpk|ibu|pak)/i', $lower)) {
            return "**Akses Dibatasi**\n\nSebagai **Siswa**, Anda tidak memiliki izin untuk mengakses data absensi, kehadiran, atau profil pribadi Guru demi menjaga privasi data sekolah.";
        }

        // 2. SISWA mencoba mengakses data kelas / siswa lain
        if ($userRole === 'siswa' && (preg_match('/(kelas|daftar siswa|semua siswa|anak kelas|murid kelas|absen kelas)\s+[0-9xvi]+/i', $lower) || preg_match('/(daftar (siswa|murid|teman)|siapa saja siswa|siapa saja murid|nomor hp teman|kontak teman)/i', $lower))) {
            return "**Akses Dibatasi**\n\nSebagai **Siswa**, Anda tidak memiliki izin untuk melihat daftar siswa atau rekap absensi kelas orang lain. Anda hanya berwenang melihat data pribadi dan riwayat kehadiran Anda sendiri.";
        }

        // 3. SISWA mencoba mengakses IoT / statistik sekolah
        if ($userRole === 'siswa' && preg_match('/(iot|perangkat|alat|scanner|ip server|ip host|rekap sekolah|statistik sistem|total siswa|total guru)/i', $lower)) {
            return "**Akses Dibatasi**\n\nInformasi teknis perangkat IoT dan statistik sistem sekolah hanya dapat diakses oleh **Administrator** dan **Kepala Sekolah**.";
        }

        // 4. GURU BIASA mencoba mengakses data siswa / absensi kelas
        if ($userRole === 'guru' && preg_match('/(siswa|murid|anak kelas|daftar kelas|absen kelas|kehadiran kelas|daftar siswa)/i', $lower)) {
            return "**Akses Terbatas**\n\nSebagai **Guru Mata Pelajaran**, Anda tidak memiliki hak akses untuk melihat data siswa atau kehadiran kelas. Hak akses data siswa diberikan kepada **Wali Kelas** (khusus kelas binaannya), serta **Kepala Sekolah** dan **Administrator**.";
        }

        // 5. GURU BIASA & WALI KELAS mencoba mengakses perangkat IoT
        if (in_array($userRole, ['guru', 'wali_kelas']) && preg_match('/(iot|perangkat|scanner|mesin absensi|alat absensi|esp32|ip host)/i', $lower)) {
            return "**Akses Terbatas**\n\nInformasi konfigurasi dan status perangkat IoT scanner hanya dapat diakses oleh **Administrator** dan **Kepala Sekolah**.";
        }

        // =====================================================================
        // DATA PRIBADI SISWA (ROLE: SISWA)
        // =====================================================================
        if ($userRole === 'siswa') {
            // A. Kartu RFID & Profil Siswa
            if (preg_match('/(kartu saya|rfid saya|kartu rfid saya|status kartu|uid saya|profil saya|data saya|nis saya)/i', $lower) ||
                (preg_match('/(rfid|kartu)/i', $lower) && preg_match('/(saya|aktif|terdaftar|punya|milik|kartuku)/i', $lower))) {
                $db->query("SELECT s.id as siswa_id, s.nis, u.name, k.nama_kelas, COALESCE(s.rfid_uid, r.uid) as rfid_uid 
                            FROM siswa s 
                            JOIN users u ON u.id = s.user_id 
                            LEFT JOIN kelas k ON k.id = s.kelas_id 
                            LEFT JOIN rfid_cards r ON r.student_id = s.id AND r.status = 'active' 
                            WHERE s.user_id = :uid");
                $db->bind(':uid', $userId);
                $siswa = $db->single();
                if (!$siswa) return false;

                $rfidBadge = $siswa->rfid_uid 
                    ? "[Terdaftar] (UID: `{$siswa->rfid_uid}`)" 
                    : "[Belum Terdaftar] Belum ada kartu RFID (Silakan hubungi operator/admin)";

                return "**Profil & Kartu Absensi Anda**\n\n"
                     . "- **Nama**       : {$siswa->name}\n"
                     . "- **NIS**        : {$siswa->nis}\n"
                     . "- **Kelas**      : " . ($siswa->nama_kelas ?? '-') . "\n"
                     . "- **Kartu RFID** : {$rfidBadge}";
            }

            // B. Status Izin / Pulang Cepat Siswa
            if (preg_match('/(izin|sakit|pulang cepat|pengajuan)/i', $lower)) {
                $db->query("SELECT s.id FROM siswa s WHERE s.user_id = :uid");
                $db->bind(':uid', $userId);
                $sRow = $db->single();
                $sid = $sRow ? $sRow->id : 0;

                $db->query("SELECT jenis, tanggal_mulai, tanggal_selesai, alasan, status FROM pengajuan_izin WHERE user_id = :uid ORDER BY id DESC LIMIT 3");
                $db->bind(':uid', $userId);
                $izinList = $db->resultSet();

                $db->query("SELECT tanggal, jam_pengajuan, alasan, status FROM pengajuan_pulang_cepat WHERE siswa_id = :sid ORDER BY id DESC LIMIT 3");
                $db->bind(':sid', $sid);
                $pcList = $db->resultSet();

                $respon = "**Status Pengajuan Izin & Pulang Cepat Anda**\n\n";
                if (!empty($izinList)) {
                    $respon .= "**Pengajuan Izin / Sakit:**\n";
                    foreach ($izinList as $iz) {
                        $stBadge = match(strtolower($iz->status)) {
                            'approved' => '[Disetujui]',
                            'rejected' => '[Ditolak]',
                            default    => '[Menunggu Persetujuan]',
                        };
                        $respon .= "- {$iz->jenis} ({$iz->tanggal_mulai}): {$stBadge} ({$iz->alasan})\n";
                    }
                } else {
                    $respon .= "- Belum ada riwayat pengajuan izin/sakit.\n";
                }

                if (!empty($pcList)) {
                    $respon .= "\n**Pengajuan Pulang Cepat:**\n";
                    foreach ($pcList as $pc) {
                        $stBadge = match(strtoupper($pc->status)) {
                            'APPROVED' => '[Disetujui]',
                            'REJECTED' => '[Ditolak]',
                            default    => '[Menunggu Persetujuan]',
                        };
                        $respon .= "- {$pc->tanggal} ({$pc->jam_pengajuan}): {$stBadge} ({$pc->alasan})\n";
                    }
                }
                return $respon;
            }

            // C. Jadwal Absensi Siswa
            if (preg_match('/(jadwal|kapan|masuk|pulang|jam)/i', $lower)) {
                $db->query("SELECT * FROM jadwal_siswa WHERE hari = :hari AND status_aktif = 1");
                $db->bind(':hari', $hariIni);
                $jadwal = $db->single();

                if (!$jadwal) {
                    return "Hari ini (**{$hariIni}**) tidak ada jadwal absensi aktif / hari libur sekolah.";
                }

                return "**Jadwal Absensi Siswa Hari Ini ({$hariIni})**\n\n"
                     . "- Mulai Absen : " . substr($jadwal->jam_mulai_absensi, 0, 5) . " WIB\n"
                     . "- Jam Masuk   : " . substr($jadwal->jam_datang, 0, 5) . " WIB\n"
                     . "- Batas Telat : " . substr($jadwal->batas_terlambat, 0, 5) . " WIB\n"
                     . "- Jam Pulang  : " . substr($jadwal->jam_pulang, 0, 5) . " WIB\n\n"
                     . "_Pastikan Anda menempelkan kartu RFID sebelum batas toleransi terlambat._";
            }

            // D. Absensi Siswa (Diri Sendiri)
            if (preg_match('/(absen|hadir|kehadiran|rekap|telat|terlambat|alfa)/i', $lower)) {
                $db->query("SELECT s.id as siswa_id, u.name FROM siswa s JOIN users u ON u.id = s.user_id WHERE s.user_id = :uid");
                $db->bind(':uid', $userId);
                $siswa = $db->single();
                if (!$siswa) return false;

                // Hari ini
                $db->query("SELECT jam_datang, jam_pulang, status, keterangan FROM absensi_siswa WHERE siswa_id = :sid AND tanggal = :tgl");
                $db->bind(':sid', $siswa->siswa_id);
                $db->bind(':tgl', $today);
                $hariIniRow = $db->single();

                // Rekap bulan ini
                $db->query("SELECT SUM(status='hadir') as hadir, SUM(status='terlambat') as terlambat,
                                   SUM(status='izin') as izin, SUM(status='sakit') as sakit, SUM(status='alfa') as alfa
                            FROM absensi_siswa WHERE siswa_id = :sid AND DATE_FORMAT(tanggal,'%Y-%m')=:bln");
                $db->bind(':sid', $siswa->siswa_id);
                $db->bind(':bln', $bulanIni);
                $summary = $db->single();

                // 7 hari terakhir
                $db->query("SELECT tanggal, jam_datang, status, keterangan FROM absensi_siswa WHERE siswa_id = :sid ORDER BY tanggal DESC LIMIT 7");
                $db->bind(':sid', $siswa->siswa_id);
                $riwayat = $db->resultSet();

                $bulanLabel = $this->namaBulan(date('n')) . ' ' . date('Y');
                $statusHariIni = $hariIniRow 
                    ? "Sudah absen ({$hariIniRow->status}, masuk " . substr($hariIniRow->jam_datang, 0, 5) . " WIB)" 
                    : "Belum tercatat absen hari ini";

                $respon = "**Rekap Kehadiran Anda — {$siswa->name}**\n"
                        . "Status Hari Ini : {$statusHariIni}\n"
                        . "Periode Bulan   : {$bulanLabel}\n\n"
                        . "- Tepat Waktu : " . ($summary->hadir     ?? 0) . " hari\n"
                        . "- Terlambat   : " . ($summary->terlambat ?? 0) . " hari\n"
                        . "- Izin        : " . ($summary->izin      ?? 0) . " hari\n"
                        . "- Sakit       : " . ($summary->sakit     ?? 0) . " hari\n"
                        . "- Alfa        : " . ($summary->alfa      ?? 0) . " hari\n";

                if (!empty($riwayat)) {
                    $respon .= "\n**Riwayat 7 Hari Terakhir:**\n";
                    foreach ($riwayat as $r) {
                        $tgl = date('d/m', strtotime($r->tanggal));
                        $jam = $r->jam_datang ? substr($r->jam_datang, 0, 5) : '--:--';
                        $statusBadge = match($r->status) {
                            'hadir'     => '[Hadir]',
                            'terlambat' => '[Terlambat]',
                            'izin'      => '[Izin]',
                            'sakit'     => '[Sakit]',
                            default     => '[Alfa]',
                        };
                        $ket = $r->keterangan ? " ({$r->keterangan})" : '';
                        $respon .= "- {$tgl}: {$statusBadge} {$r->status} ({$jam}){$ket}\n";
                    }
                }
                return $respon;
            }
        }

        // =====================================================================
        // DATA PRIBADI GURU & WALI KELAS
        // =====================================================================
        if (in_array($userRole, ['guru', 'wali_kelas'])) {
            // A. Profil & Barcode Diri Sendiri
            if (preg_match('/(barcode|profil saya|nip saya|mapel saya)/i', $lower)) {
                $db->query("SELECT g.nip, g.barcode_id, g.subject, u.name, k.nama_kelas 
                            FROM guru g 
                            JOIN users u ON u.id = g.user_id 
                            LEFT JOIN kelas k ON k.wali_kelas_id = u.id 
                            WHERE g.user_id = :uid");
                $db->bind(':uid', $userId);
                $gData = $db->single();
                if ($gData) {
                    $wkInfo = $gData->nama_kelas ? "- **Wali Kelas** : {$gData->nama_kelas}\n" : "";
                    return "**Profil Guru Anda**\n\n"
                         . "- **Nama**       : {$gData->name}\n"
                         . "- **NIP**        : " . ($gData->nip ?? '-') . "\n"
                         . "- **Mapel**      : " . ($gData->subject ?? 'Umum') . "\n"
                         . "- **Barcode ID** : `{$gData->barcode_id}`\n"
                         . $wkInfo
                         . "\n_Barcode ID digunakan saat scan presensi guru di monitor guru._";
                }
            }

            // B. Jadwal Mengajar / Kehadiran Guru Diri Sendiri
            if (preg_match('/(jadwal|kapan saya|jam masuk saya|jam pulang saya|jadwal mengajar|jadwal hari ini|jadwal minggu)/i', $lower)) {
                $db->query("SELECT g.id as guru_id, u.name FROM guru g JOIN users u ON u.id = g.user_id WHERE g.user_id = :uid");
                $db->bind(':uid', $userId);
                $guru = $db->single();

                if ($guru) {
                    if (preg_match('/(hari ini|sekarang)/i', $lower)) {
                        $db->query("SELECT * FROM jadwal_guru WHERE guru_id = :gid AND hari = :hari AND is_active = 1");
                        $db->bind(':gid', $guru->guru_id);
                        $db->bind(':hari', $hariIni);
                        $jToday = $db->single();

                        if (!$jToday) {
                            return "Jadwal hari ini (**{$hariIni}**) tidak aktif / libur untuk Bapak/Ibu **{$guru->name}**.";
                        }
                        return "**Jadwal Anda Hari Ini ({$hariIni})**\n\n"
                             . "- Jam Masuk   : " . substr($jToday->jam_datang, 0, 5) . " WIB\n"
                             . "- Batas Telat : " . substr($jToday->batas_terlambat, 0, 5) . " WIB\n"
                             . "- Jam Pulang  : " . substr($jToday->jam_pulang, 0, 5) . " WIB";
                    }

                    $db->query("SELECT * FROM jadwal_guru WHERE guru_id = :gid AND is_active = 1 ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')");
                    $db->bind(':gid', $guru->guru_id);
                    $jadwalGuru = $db->resultSet();

                    if (empty($jadwalGuru)) {
                        return "Belum ada jadwal mengajar aktif yang tercatat untuk Anda, **{$guru->name}**.";
                    }

                    $respon = "**Jadwal Mingguan Anda — {$guru->name}**\n\n";
                    foreach ($jadwalGuru as $j) {
                        $tandaHariIni = ($j->hari === $hariIni) ? ' [Hari Ini]' : '';
                        $respon .= "- **{$j->hari}**{$tandaHariIni}: Masuk " . substr($j->jam_datang, 0, 5) . " | Telat " . substr($j->batas_terlambat, 0, 5) . " | Pulang " . substr($j->jam_pulang, 0, 5) . "\n";
                    }
                    return $respon;
                }
            }

            // C. Absensi Guru Diri Sendiri (Jika menanyakan absensi pribadi)
            $isTanyaAbsenPribadi = ($userRole === 'guru' && preg_match('/(absen|hadir|kehadiran|rekap)/i', $lower)) ||
                                   ($userRole === 'wali_kelas' && preg_match('/(absen saya|rekap saya|kehadiran saya|absenku|riwayat absen saya)/i', $lower));

            if ($isTanyaAbsenPribadi) {
                $db->query("SELECT g.id as guru_id, u.name FROM guru g JOIN users u ON u.id = g.user_id WHERE g.user_id = :uid");
                $db->bind(':uid', $userId);
                $guru = $db->single();
                if (!$guru) return false;

                $db->query("SELECT tanggal, jam_datang, jam_pulang, status, keterangan FROM absensi_guru WHERE guru_id = :gid AND DATE_FORMAT(tanggal,'%Y-%m') = :bln ORDER BY tanggal DESC LIMIT 15");
                $db->bind(':gid', $guru->guru_id);
                $db->bind(':bln', $bulanIni);
                $riwayat = $db->resultSet();

                $db->query("SELECT SUM(status='hadir') as hadir, SUM(status='terlambat') as terlambat,
                                   SUM(status='izin') as izin, SUM(status='sakit') as sakit, SUM(status='alfa') as alfa
                            FROM absensi_guru WHERE guru_id = :gid AND DATE_FORMAT(tanggal,'%Y-%m')=:bln");
                $db->bind(':gid', $guru->guru_id);
                $db->bind(':bln', $bulanIni);
                $summary = $db->single();

                $bulanLabel = $this->namaBulan(date('n')) . ' ' . date('Y');
                $respon = "**Rekap Kehadiran Anda — {$guru->name}**\n"
                        . "Periode: {$bulanLabel}\n\n"
                        . "- Tepat Waktu : " . ($summary->hadir     ?? 0) . " hari\n"
                        . "- Terlambat   : " . ($summary->terlambat ?? 0) . " hari\n"
                        . "- Izin        : " . ($summary->izin      ?? 0) . " hari\n"
                        . "- Sakit       : " . ($summary->sakit     ?? 0) . " hari\n"
                        . "- Alfa        : " . ($summary->alfa      ?? 0) . " hari\n";

                if (!empty($riwayat)) {
                    $respon .= "\n**Riwayat 15 Hari Terakhir:**\n";
                    foreach ($riwayat as $r) {
                        $tgl = date('d/m', strtotime($r->tanggal));
                        $jam = $r->jam_datang ? substr($r->jam_datang, 0, 5) : '--:--';
                        $statusBadge = match($r->status) {
                            'hadir'     => '[Hadir]',
                            'terlambat' => '[Terlambat]',
                            'izin'      => '[Izin]',
                            'sakit'     => '[Sakit]',
                            default     => '[Alfa]',
                        };
                        $respon .= "- {$tgl}: {$statusBadge} {$r->status} (masuk {$jam})\n";
                    }
                }
                return $respon;
            }
        }

        // =====================================================================
        // DATA KHUSUS WALI KELAS (AKSES DATA SISWA KELAS BINAANNYA)
        // =====================================================================
        if ($userRole === 'wali_kelas') {
            // Ambil kelas binaan wali kelas yang sedang login
            $db->query("SELECT id, nama_kelas, class_code FROM kelas WHERE wali_kelas_id = :uid LIMIT 1");
            $db->bind(':uid', $userId);
            $myKelas = $db->single();

            // Hanya proses query data siswa jika secara spesifik menanyakan siswa kelas, absen kelas, atau izin
            $isTanyaSiswaKelas = preg_match('/(siswa|murid|anak kelas|kelasku|kelas saya|daftar kelas|absen kelas|kehadiran kelas|absen siswa|kehadiran siswa)/i', $lower) ||
                                 (preg_match('/(izin|pulang cepat|pengajuan)/i', $lower) && preg_match('/(siswa|murid|anak|kelas)/i', $lower)) ||
                                 preg_match('/(siapa.*(hadir|masuk|datang|absen)|absen hari ini)/i', $lower);

            if ($isTanyaSiswaKelas) {
                if (!$myKelas) {
                    return "Anda terdaftar sebagai **Wali Kelas**, namun belum ada kelas binaan yang ditugaskan kepada Anda oleh Administrator.";
                }

                // Cek jika wali kelas mencoba mencari nama kelas lain
                if (preg_match('/(kelas|anak kelas)\s+([0-9xvi]+[\w\s]*?)(?:\s+siapa|\s+aja|\s+saja|\s+yang|\?|$)/i', $lower, $mMatch)) {
                    $targetKelas = trim(preg_replace('/(hari ini|yang|hadir|absen|masuk|siapa|saja|aja).*/i', '', $mMatch[2]));
                    if (!empty($targetKelas) && stripos($myKelas->nama_kelas, $targetKelas) === false && stripos($targetKelas, $myKelas->nama_kelas) === false) {
                        return "**Akses Terbatas**\n\nSebagai Wali Kelas, Anda hanya berwenang mengakses data siswa di kelas binaan Anda (**{$myKelas->nama_kelas}**). Anda tidak memiliki izin untuk melihat data kelas lain.";
                    }
                }

                // A. Izin / Pulang Cepat Siswa Kelas Binaan
                if (preg_match('/(izin|sakit|pulang cepat|pengajuan|permohonan)/i', $lower)) {
                    $db->query("SELECT pi.*, u.name as nama_siswa 
                                FROM pengajuan_izin pi 
                                JOIN users u ON u.id = pi.user_id 
                                JOIN siswa s ON s.user_id = u.id 
                                WHERE s.kelas_id = :kid AND pi.status = 'pending' 
                                ORDER BY pi.created_at DESC");
                    $db->bind(':kid', $myKelas->id);
                    $pendingIzin = $db->resultSet();

                    $db->query("SELECT pc.*, u.name as nama_siswa 
                                FROM pengajuan_pulang_cepat pc 
                                JOIN siswa s ON s.id = pc.siswa_id 
                                JOIN users u ON u.id = s.user_id 
                                WHERE s.kelas_id = :kid AND pc.status IN ('PENDING', 'DRAFT') 
                                ORDER BY pc.created_at DESC");
                    $db->bind(':kid', $myKelas->id);
                    $pendingPc = $db->resultSet();

                    $respon = "**Pengajuan Izin & Pulang Cepat Siswa — Kelas {$myKelas->nama_kelas}**\n\n";
                    if (empty($pendingIzin) && empty($pendingPc)) {
                        $respon .= "Tidak ada pengajuan izin atau permohonan pulang cepat yang menunggu persetujuan.";
                    } else {
                        if (!empty($pendingIzin)) {
                            $respon .= "**Pengajuan Izin Pending (" . count($pendingIzin) . "):**\n";
                            foreach ($pendingIzin as $pi) {
                                $respon .= "- **{$pi->nama_siswa}** ({$pi->jenis}): {$pi->alasan} [{$pi->tanggal_mulai}]\n";
                            }
                        }
                        if (!empty($pendingPc)) {
                            $respon .= "\n**Permohonan Pulang Cepat Pending (" . count($pendingPc) . "):**\n";
                            foreach ($pendingPc as $pc) {
                                $respon .= "- **{$pc->nama_siswa}**: {$pc->alasan} (Jam {$pc->jam_pengajuan})\n";
                            }
                        }
                        $respon .= "\n_Silakan periksa menu Izin Siswa atau Pulang Cepat untuk menyetujui._";
                    }
                    return $respon;
                }

                // B. Kehadiran Hari Ini Kelas Binaan
                $isTanyaKehadiran = preg_match('/(hadir|masuk|datang|kehadiran)/i', $lower) || preg_match('/(siapa.*(hadir|masuk|datang|absen)|absen hari ini)/i', $lower);
                if ($isTanyaKehadiran) {
                    $db->query("SELECT u.name, ab.jam_datang, ab.status 
                                FROM absensi_siswa ab 
                                JOIN siswa s ON s.id = ab.siswa_id 
                                JOIN users u ON u.id = s.user_id 
                                WHERE s.kelas_id = :kid AND ab.tanggal = :tgl AND ab.status IN ('hadir', 'terlambat') 
                                ORDER BY ab.jam_datang ASC");
                    $db->bind(':kid', $myKelas->id);
                    $db->bind(':tgl', $today);
                    $hadirList = $db->resultSet();

                    $db->query("SELECT u.name, s.nis 
                                FROM siswa s 
                                JOIN users u ON u.id = s.user_id 
                                WHERE s.kelas_id = :kid AND s.id NOT IN (
                                    SELECT siswa_id FROM absensi_siswa WHERE tanggal = :tgl AND status IN ('hadir', 'terlambat')
                                ) ORDER BY u.name ASC");
                    $db->bind(':kid', $myKelas->id);
                    $db->bind(':tgl', $today);
                    $belumHadirList = $db->resultSet();

                    $respon = "**Kehadiran Siswa Kelas {$myKelas->nama_kelas} Hari Ini**\n"
                            . "Tanggal: " . date('d/m/Y') . " ({$hariIni})\n\n"
                            . "- Sudah Hadir : " . count($hadirList) . " siswa\n"
                            . "- Belum Hadir : " . count($belumHadirList) . " siswa\n\n";

                    if (!empty($hadirList)) {
                        $respon .= "**Siswa yang Hadir:**\n";
                        foreach ($hadirList as $i => $s) {
                            $statusBadge = $s->status === 'terlambat' ? '[Terlambat]' : '[Hadir]';
                            $respon .= ($i + 1) . ". {$s->name} — " . substr($s->jam_datang, 0, 5) . " {$statusBadge}\n";
                        }
                    }

                    if (!empty($belumHadirList)) {
                        $respon .= "\n**Siswa yang Belum Absen:**\n";
                        foreach ($belumHadirList as $i => $b) {
                            $respon .= "- {$b->name}\n";
                        }
                    }
                    return $respon;
                }

                // C. Daftar Seluruh Siswa Kelas Binaan
                if (preg_match('/(daftar|semua|anggota|nama siswa|murid)/i', $lower)) {
                    $db->query("SELECT u.name, s.nis, r.uid as rfid_uid 
                                FROM siswa s 
                                JOIN users u ON u.id = s.user_id 
                                LEFT JOIN rfid_cards r ON r.student_id = s.id AND r.status = 'active' 
                                WHERE s.kelas_id = :kid ORDER BY u.name ASC");
                    $db->bind(':kid', $myKelas->id);
                    $siswaAll = $db->resultSet();

                    if (empty($siswaAll)) {
                        return "Belum ada siswa yang terdaftar di kelas binaan Anda (**{$myKelas->nama_kelas}**).";
                    }

                    $respon = "**Daftar Siswa Kelas {$myKelas->nama_kelas}** (" . count($siswaAll) . " siswa):\n\n";
                    foreach ($siswaAll as $i => $s) {
                        $rfid = $s->rfid_uid ? "RFID: `{$s->rfid_uid}`" : "[Belum Ada RFID]";
                        $respon .= ($i + 1) . ". {$s->name} (NIS: {$s->nis}) — {$rfid}\n";
                    }
                    return $respon;
                }
            }
        }

        // =====================================================================
        // DATA KHUSUS ADMIN & KEPALA SEKOLAH (SUPERUSER FULL ACCESS)
        // =====================================================================
        if (in_array($userRole, ['admin', 'kepsek'])) {
            // A. Perangkat IoT Scanner & Hardware
            if (preg_match('/(iot|perangkat|scanner|mesin absensi|alat|esp32|ip host)/i', $lower)) {
                $db->query("SELECT device_name, ip_address, wifi_ssid, status, last_seen FROM iot_devices ORDER BY id ASC");
                $devices = $db->resultSet();

                if (empty($devices)) {
                    return "**Perangkat IoT Absensi**\n\nBelum ada perangkat scanner IoT yang terdaftar di sistem.";
                }

                $respon = "**Status Perangkat IoT Scanner Absensi** (" . count($devices) . " Perangkat)\n\n";
                foreach ($devices as $i => $d) {
                    $isOnline = ($d->last_seen && (time() - strtotime($d->last_seen) <= 120));
                    $statusBadge = $isOnline ? "[Online]" : "[Offline]";
                    $lastSeen = $d->last_seen ? date('d/m H:i:s', strtotime($d->last_seen)) : 'Belum pernah';

                    $respon .= ($i + 1) . ". **{$d->device_name}** — {$statusBadge}\n"
                             . "   - Host IP : `{$d->ip_address}`\n"
                             . "   - WiFi    : `{$d->wifi_ssid}`\n"
                             . "   - Terakhir: {$lastSeen}\n\n";
                }
                $respon .= "_Status Online otomatis diperbarui setiap kali alat mengirimkan heartbeat._";
                return $respon;
            }

            // B. Kehadiran Guru Hari Ini (Seluruh Guru)
            if (preg_match('/(guru.*(hadir|absen|masuk)|siapa.*guru.*hadir|absen guru hari ini|kehadiran guru)/i', $lower)) {
                $db->query("SELECT u.name, g.nip, ag.jam_datang, ag.jam_pulang, ag.status 
                            FROM absensi_guru ag 
                            JOIN guru g ON g.id = ag.guru_id 
                            JOIN users u ON u.id = g.user_id 
                            WHERE ag.tanggal = :tgl 
                            ORDER BY ag.jam_datang ASC");
                $db->bind(':tgl', $today);
                $guruHadir = $db->resultSet();

                $db->query("SELECT COUNT(*) as total FROM guru");
                $totalGuru = $db->single()->total ?? 0;

                $respon = "**Rekap Kehadiran Guru Hari Ini ({$hariIni}, " . date('d/m/Y') . ")**\n"
                        . "Total Hadir: " . count($guruHadir) . " dari {$totalGuru} Guru\n\n";

                if (empty($guruHadir)) {
                    $respon .= "Belum ada guru yang tercatat hadir hari ini.";
                } else {
                    foreach ($guruHadir as $i => $gh) {
                        $statusBadge = $gh->status === 'terlambat' ? '[Terlambat]' : '[Tepat Waktu]';
                        $respon .= ($i + 1) . ". **{$gh->name}** — Masuk: " . substr($gh->jam_datang, 0, 5) . " {$statusBadge}\n";
                    }
                }
                return $respon;
            }

            // B.1 Daftar Lengkap Guru (0 Token, Format Rapi, Padat & Lengkap)
            if (preg_match('/((daftar|data|list|nama|siapa\s+(saja|aja)|semua)\s+.*?(guru|pengajar|pendidik))|((guru|pengajar)\s+.*?(lengkap|semua|daftar|list|ada\s+siapa|siapa\s+saja))|^(guru|guru\s+guru)$/i', $lower)) {
                
                $db->query("SELECT u.name, g.nip, g.subject, k.nama_kelas as wali_kelas
                            FROM guru g 
                            JOIN users u ON u.id = g.user_id 
                            LEFT JOIN kelas k ON k.wali_kelas_id = u.id 
                            ORDER BY u.name ASC");
                $allGuru = $db->resultSet();

                if (empty($allGuru)) {
                    return "**Data Guru**\n\nBelum ada data guru yang terdaftar di sistem.";
                }

                $tot = count($allGuru);
                $respon = "**Daftar Guru SMK NU Wargabinangun** (Total: {$tot} Guru)\n\n";
                foreach ($allGuru as $i => $g) {
                    $no = $i + 1;
                    $mapel = (!empty($g->subject) && $g->subject !== '-') ? " — Mapel: {$g->subject}" : "";
                    $wali  = !empty($g->wali_kelas) ? " [Wali Kelas {$g->wali_kelas}]" : "";
                    $nip   = (!empty($g->nip) && $g->nip !== '-') ? " (NIP: {$g->nip})" : "";
                    $respon .= "{$no}. **{$g->name}**{$mapel}{$wali}{$nip}\n";
                }
                return $respon;
            }

            // C. Query Kelas Tertentu (Admin & Kepsek bisa cek kelas MANAPUN)
            if (preg_match('/(daftar|data|list|semua|ada)\s+kelas/i', $lower) || preg_match('/kelas\s+(apa\s+(saja|aja)|semua|lengkap)/i', $lower)) {
                $db->query("SELECT k.nama_kelas, k.tingkat, k.jurusan, u.name as wali_nama,
                                   (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa
                            FROM kelas k 
                            LEFT JOIN users u ON u.id = k.wali_kelas_id 
                            ORDER BY k.nama_kelas ASC");
                $kelasList = $db->resultSet();

                if (empty($kelasList)) {
                    return "**Data Kelas**\n\nBelum ada data kelas yang terdaftar di sistem.";
                }

                $totK = count($kelasList);
                $respon = "**Daftar Kelas SMK NU Wargabinangun** (Total: {$totK} Kelas)\n\n";
                foreach ($kelasList as $i => $k) {
                    $no = $i + 1;
                    $wali = !empty($k->wali_nama) ? "Wali: {$k->wali_nama}" : "Wali: -";
                    $respon .= "{$no}. **{$k->nama_kelas}** ({$k->total_siswa} Siswa) — {$wali}\n";
                }
                return $respon;
            }

            if (preg_match('/(kelas|anak|siswa|murid)\s+([0-9xvi]+\s+[a-z0-9\s]+?)(?:\s+siapa|\s+aja|\s+saja|\s+yang|\?|$)/i', $lower, $m)) {
                $namaKelasRaw = trim($m[2]);
                $namaKelas    = preg_replace('/(hari ini|yang|hadir|absen|masuk|siapa|saja|aja).*/i', '', $namaKelasRaw);
                $namaKelas    = trim($namaKelas);

                if (!empty($namaKelas)) {
                    $db->query("SELECT k.id, k.nama_kelas, u.name as wali_nama 
                                FROM kelas k 
                                LEFT JOIN users u ON u.id = k.wali_kelas_id 
                                WHERE LOWER(k.nama_kelas) LIKE :n LIMIT 1");
                    $db->bind(':n', '%' . $namaKelas . '%');
                    $kelas = $db->single();

                    if ($kelas) {
                        $isHadir = preg_match('/(hadir|absen|masuk|hari ini)/i', $lower);
                        if ($isHadir) {
                            $db->query("SELECT u.name, ab.jam_datang, ab.status
                                        FROM absensi_siswa ab
                                        JOIN siswa s ON s.id = ab.siswa_id
                                        JOIN users u ON u.id = s.user_id
                                        WHERE s.kelas_id = :kid AND ab.tanggal = :tgl AND ab.status IN ('hadir','terlambat')
                                        ORDER BY ab.jam_datang ASC");
                            $db->bind(':kid', $kelas->id);
                            $db->bind(':tgl', $today);
                            $hadirList = $db->resultSet();

                            $respon = "**Kehadiran Siswa Kelas {$kelas->nama_kelas}**\n"
                                    . "Wali Kelas: " . ($kelas->wali_nama ?? 'Belum ada') . "\n"
                                    . "Tanggal: " . date('d/m/Y') . " (" . count($hadirList) . " hadir):\n\n";

                            if (empty($hadirList)) {
                                $respon .= "Belum ada siswa kelas **{$kelas->nama_kelas}** yang tercatat hadir hari ini.";
                            } else {
                                foreach ($hadirList as $i => $s) {
                                    $statusBadge = $s->status === 'terlambat' ? '[Terlambat]' : '[Hadir]';
                                    $respon .= ($i + 1) . ". {$s->name} — " . substr($s->jam_datang, 0, 5) . " {$statusBadge}\n";
                                }
                            }
                            return $respon;
                        } else {
                            $db->query("SELECT u.name, s.nis, r.uid as rfid_uid 
                                        FROM siswa s 
                                        JOIN users u ON u.id = s.user_id 
                                        LEFT JOIN rfid_cards r ON r.student_id = s.id AND r.status = 'active' 
                                        WHERE s.kelas_id = :kid ORDER BY u.name ASC");
                            $db->bind(':kid', $kelas->id);
                            $siswaAll = $db->resultSet();

                            if (empty($siswaAll)) return "Belum ada siswa di kelas **{$kelas->nama_kelas}**.";
                            $respon = "**Daftar Siswa Kelas {$kelas->nama_kelas}** (" . count($siswaAll) . " siswa)\n"
                                    . "Wali Kelas: " . ($kelas->wali_nama ?? 'Belum ada') . ":\n\n";
                            foreach ($siswaAll as $i => $s) {
                                $rfid = $s->rfid_uid ? "RFID: `{$s->rfid_uid}`" : "[Belum Ada RFID]";
                                $respon .= ($i + 1) . ". {$s->name} (NIS: {$s->nis}) — {$rfid}\n";
                            }
                            return $respon;
                        }
                    }
                }
            }

            // Spesifik: Berapa jumlah / total guru
            if (preg_match('/(jumlah\s+guru|total\s+guru|ada\s+berapa\s+guru|berapa\s+guru|banyaknya\s+guru|guru\s+ada\s+berapa)/i', $lower)) {
                $db->query("SELECT COUNT(*) as total FROM guru");
                $totGuru = $db->single()->total ?? 0;
                $db->query("SELECT COUNT(*) as hadir FROM absensi_guru WHERE tanggal = :tgl");
                $db->bind(':tgl', $today);
                $hadirGuru = $db->single()->hadir ?? 0;
                return "Saat ini terdapat **{$totGuru} guru** yang terdaftar di SMK NU Wargabinangun.\n\nHari ini ({$hariIni}, " . date('d/m/Y') . "), tercatat **{$hadirGuru} guru** telah hadir.";
            }

            // Spesifik: Berapa jumlah / total siswa
            if (preg_match('/(jumlah\s+siswa|total\s+siswa|ada\s+berapa\s+siswa|berapa\s+siswa|banyaknya\s+siswa|siswa\s+ada\s+berapa)/i', $lower)) {
                $db->query("SELECT COUNT(*) as total FROM siswa");
                $totSiswa = $db->single()->total ?? 0;
                $db->query("SELECT COUNT(*) as hadir FROM absensi_siswa WHERE tanggal = :tgl");
                $db->bind(':tgl', $today);
                $hadirSiswa = $db->single()->hadir ?? 0;
                return "Saat ini terdapat **{$totSiswa} siswa** yang terdaftar di SMK NU Wargabinangun.\n\nHari ini ({$hariIni}, " . date('d/m/Y') . "), tercatat **{$hadirSiswa} siswa** telah hadir.";
            }

            // Spesifik: Berapa jumlah kelas
            if (preg_match('/(jumlah\s+kelas|total\s+kelas|ada\s+berapa\s+kelas|berapa\s+kelas)/i', $lower)) {
                $db->query("SELECT COUNT(*) as total FROM kelas");
                $totKelas = $db->single()->total ?? 0;
                return "SMK NU Wargabinangun memiliki total **{$totKelas} kelas** aktif (Jurusan TKJ, TKR, dan TSM).";
            }

            // D. Rekap & Statistik Keseluruhan Sekolah
            if (preg_match('/(rekap|statistik|ringkasan|semua data|rekapitulasi|laporan hari ini)/i', $lower)) {
                $db->query("SELECT COUNT(*) as total FROM siswa"); $totSiswa = $db->single()->total ?? 0;
                $db->query("SELECT COUNT(*) as total FROM guru");  $totGuru  = $db->single()->total ?? 0;
                $db->query("SELECT COUNT(*) as total FROM kelas"); $totKelas = $db->single()->total ?? 0;
                $db->query("SELECT COUNT(*) as total, SUM(IF(last_seen >= NOW() - INTERVAL 2 MINUTE, 1, 0)) as online FROM iot_devices"); 
                $iotStat = $db->single();
                $totIot = $iotStat->total ?? 0;
                $onlineIot = $iotStat->online ?? 0;

                $db->query("SELECT COUNT(*) as total, SUM(status='hadir') as hadir, SUM(status='terlambat') as telat FROM absensi_siswa WHERE tanggal = :tgl");
                $db->bind(':tgl', $today);
                $absenSiswa = $db->single();

                $db->query("SELECT COUNT(*) as total FROM absensi_guru WHERE tanggal = :tgl");
                $db->bind(':tgl', $today);
                $absenGuru = $db->single();

                $db->query("SELECT COUNT(*) as total FROM pengajuan_izin WHERE status = 'pending'");
                $pendingIzin = $db->single()->total ?? 0;

                $db->query("SELECT COUNT(*) as total FROM pengajuan_pulang_cepat WHERE status IN ('PENDING', 'DRAFT')");
                $pendingPc = $db->single()->total ?? 0;

                return "**Ringkasan Eksekutif Sistem Absensi ({$hariIni}, " . date('d/m/Y') . ")**\n\n"
                     . "- **Siswa Terdaftar** : {$totSiswa} siswa\n"
                     . "- **Guru Terdaftar**  : {$totGuru} guru\n"
                     . "- **Total Kelas**     : {$totKelas} kelas\n"
                     . "- **Perangkat IoT**   : {$totIot} alat ({$onlineIot} Online)\n\n"
                     . "**Aktivitas Hari Ini:**\n"
                     . "- Siswa Hadir  : " . ($absenSiswa->total ?? 0) . " (Tepat: " . ($absenSiswa->hadir ?? 0) . ", Telat: " . ($absenSiswa->telat ?? 0) . ")\n"
                     . "- Guru Hadir   : " . ($absenGuru->total ?? 0) . " guru\n"
                     . "- Izin Pending : {$pendingIzin} permohonan\n"
                     . "- Pulang Cepat : {$pendingPc} permohonan\n\n"
                     . "_Data sinkron langsung dari database MySQL._";
            }
        }

        // =====================================================================
        // JADWAL UMUM SEKOLAH (DAPAT DIAKSES OLEH SEMUA PERAN)
        // =====================================================================
        if (preg_match('/(jadwal\s+(absensi|masuk|sekolah|siswa|pelajaran|kbm|minggu)|jam\s+(masuk|pulang|operasional)\s+(sekolah|siswa)|jam operasional)/i', $lower)) {
            $db->query("SELECT * FROM jadwal_siswa WHERE status_aktif = 1 ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')");
            $jadwalList = $db->resultSet();
            if (empty($jadwalList)) return "Belum ada jadwal absensi sekolah yang aktif di sistem.";

            $respon = "**Jadwal Absensi Siswa & KBM Sekolah (Aktif)**\n\n";
            foreach ($jadwalList as $j) {
                $tanda = ($j->hari === $hariIni) ? ' [Hari Ini]' : '';
                $respon .= "- **{$j->hari}**{$tanda}: Mulai " . substr($j->jam_mulai_absensi, 0, 5) . " | Masuk " . substr($j->jam_datang, 0, 5) . " | Telat " . substr($j->batas_terlambat, 0, 5) . " | Pulang " . substr($j->jam_pulang, 0, 5) . "\n";
            }
            return $respon;
        }

        return false;
    }

    // =========================================================
    // QUERY PRESENSI, IZIN, SAKIT, & REKAP BERDASARKAN TANGGAL (STRICT RBAC)
    // =========================================================
    private function handleDateAttendanceQuery($message, $userId, $userRole, $db) {
        $lower = strtolower($message);

        // 1. Cek intensi absensi / izin / sakit / rekap
        $isAttendanceIntent = preg_match('/(izin|ijin|sakit|pulang\s*cepat|hadir|masuk|terlambat|telat|alfa|tidak\s*(masuk|hadir)|bolos|absen|kehadiran|presensi|rekap)/i', $lower);
        if (!$isAttendanceIntent) {
            return false;
        }

        // 2. Deteksi tanggal target
        $targetDate = $this->parseDateFromMessage($message);
        if (!$targetDate) {
            if (preg_match('/(siapa.*(izin|ijin|sakit|pulang\s*cepat|hadir|terlambat|telat|alfa|tidak\s*masuk)|ada\s*yang\s*(izin|ijin|sakit)|rekap\s*(absen|kehadiran))/i', $lower)) {
                $targetDate = date('Y-m-d');
            } else {
                return false;
            }
        }

        $tglIndo = $this->formatTanggalIndo($targetDate);

        // 3. PENEGAKAN STRICT RBAC SEBELUM QUERY
        // A. SISWA (Hanya data pribadi sendiri)
        if ($userRole === 'siswa') {
            if (preg_match('/(siapa|daftar|semua|teman|orang\s*lain|sekelas)/i', $lower) || preg_match('/(kelas)\s+[0-9xvi]+/i', $lower)) {
                return "**Akses Dibatasi**\n\nSebagai **Siswa**, Anda tidak memiliki izin untuk melihat data absensi atau izin siswa lain demi menjaga privasi sekolah. Anda hanya dapat melihat riwayat absensi pribadi Anda.";
            }

            $db->query("SELECT s.id as siswa_id, u.name, k.nama_kelas 
                        FROM siswa s 
                        JOIN users u ON u.id = s.user_id 
                        LEFT JOIN kelas k ON k.id = s.kelas_id 
                        WHERE s.user_id = :uid");
            $db->bind(':uid', $userId);
            $mySelf = $db->single();
            if (!$mySelf) return false;

            $db->query("SELECT status, jam_datang, jam_pulang, keterangan FROM absensi_siswa WHERE siswa_id = :sid AND tanggal = :tgl");
            $db->bind(':sid', $mySelf->siswa_id);
            $db->bind(':tgl', $targetDate);
            $myAbsen = $db->single();

            $db->query("SELECT jenis, alasan, status FROM pengajuan_izin WHERE user_id = :uid AND (:tgl BETWEEN tanggal_mulai AND tanggal_selesai OR tanggal_mulai = :tgl)");
            $db->bind(':uid', $userId);
            $db->bind(':tgl', $targetDate);
            $myIzin = $db->single();

            $db->query("SELECT alasan, status, jam_pengajuan FROM pengajuan_pulang_cepat WHERE siswa_id = :sid AND tanggal = :tgl");
            $db->bind(':sid', $mySelf->siswa_id);
            $db->bind(':tgl', $targetDate);
            $myPc = $db->single();

            $statusText = $myAbsen ? ucfirst($myAbsen->status) : 'Belum Tercatat';
            $jamDatang  = ($myAbsen && $myAbsen->jam_datang) ? substr($myAbsen->jam_datang, 0, 5) . " WIB" : "-";
            $jamPulang  = ($myAbsen && $myAbsen->jam_pulang) ? substr($myAbsen->jam_pulang, 0, 5) . " WIB" : "-";
            $ketText    = ($myAbsen && $myAbsen->keterangan) ? $this->cleanKeterangan($myAbsen->keterangan) : "-";

            $respon = "**Status Presensi Anda — {$tglIndo}**\n\n"
                    . "- **Nama**       : {$mySelf->name}\n"
                    . "- **Kelas**      : " . ($mySelf->nama_kelas ?? '-') . "\n"
                    . "- **Status**     : {$statusText}\n"
                    . "- **Jam Masuk**  : {$jamDatang}\n"
                    . "- **Jam Pulang** : {$jamPulang}\n"
                    . "- **Keterangan** : {$ketText}";

            if ($myIzin) {
                $respon .= "\n- **Pengajuan Izin** : " . ucfirst($myIzin->jenis) . " [{$myIzin->status}] ({$myIzin->alasan})";
            }
            if ($myPc) {
                $respon .= "\n- **Pulang Cepat**   : [{$myPc->status}] ({$myPc->alasan}) Jam: " . substr($myPc->jam_pengajuan, 0, 5);
            }
            return $respon;
        }

        // B. GURU BIASA (Dilarang intip siswa, hanya data presensi guru)
        if ($userRole === 'guru') {
            if (preg_match('/(siswa|murid|anak|kelas)/i', $lower) || (!preg_match('/(guru|pengajar|saya|pribadi)/i', $lower) && preg_match('/siapa/i', $lower))) {
                return "**Akses Terbatas**\n\nSebagai **Guru Mata Pelajaran**, Anda tidak memiliki hak akses untuk melihat data kehadiran siswa. Hak akses kehadiran siswa diberikan kepada **Wali Kelas** (khusus kelas binaannya), **Kepala Sekolah**, dan **Administrator**.";
            }

            $db->query("SELECT u.name, g.subject, ag.jam_datang, ag.status, ag.keterangan 
                        FROM absensi_guru ag 
                        JOIN guru g ON g.id = ag.guru_id 
                        JOIN users u ON u.id = g.user_id 
                        WHERE ag.tanggal = :tgl 
                        ORDER BY ag.jam_datang ASC");
            $db->bind(':tgl', $targetDate);
            $guruRows = $db->resultSet();

            $respon = "**Kehadiran Guru — {$tglIndo}**\n\n";
            if (empty($guruRows)) {
                $respon .= "Belum ada catatan presensi guru pada tanggal {$tglIndo}.";
            } else {
                foreach ($guruRows as $i => $gr) {
                    $no = $i + 1;
                    $stBadge = match($gr->status) {
                        'hadir'     => '[Tepat Waktu]',
                        'terlambat' => '[Terlambat]',
                        'izin'      => '[Izin]',
                        'sakit'     => '[Sakit]',
                        default     => '[' . ucfirst($gr->status) . ']',
                    };
                    $jam = $gr->jam_datang ? " (" . substr($gr->jam_datang, 0, 5) . " WIB)" : "";
                    $mapel = ($gr->subject && $gr->subject !== '-') ? " ({$gr->subject})" : "";
                    $respon .= "{$no}. **{$gr->name}**{$mapel} — {$stBadge}{$jam}\n";
                }
            }
            return $respon;
        }

        // C. WALI KELAS (Khusus kelas binaannya)
        $filterKelasId = null;
        $namaKelasBinaan = null;
        if ($userRole === 'wali_kelas') {
            $db->query("SELECT id, nama_kelas FROM kelas WHERE wali_kelas_id = :uid LIMIT 1");
            $db->bind(':uid', $userId);
            $myKelas = $db->single();
            if (!$myKelas) {
                return "Anda belum ditugaskan sebagai Wali Kelas pada kelas tertentu oleh Administrator.";
            }

            if (preg_match('/(kelas|anak kelas)\s+([0-9xvi]+[\w\s]*?)(?:\s+siapa|\s+aja|\s+saja|\s+yang|\?|$)/i', $lower, $mMatch)) {
                $targetKelas = trim(preg_replace('/(hari ini|yang|hadir|absen|masuk|siapa|saja|aja).*/i', '', $mMatch[2]));
                if (!empty($targetKelas) && stripos($myKelas->nama_kelas, $targetKelas) === false && stripos($targetKelas, $myKelas->nama_kelas) === false) {
                    return "**Akses Terbatas**\n\nSebagai Wali Kelas, Anda hanya berwenang mengakses data siswa di kelas binaan Anda (**{$myKelas->nama_kelas}**). Anda tidak memiliki izin untuk melihat data kelas lain.";
                }
            }

            $filterKelasId = $myKelas->id;
            $namaKelasBinaan = $myKelas->nama_kelas;
        }

        // D. ADMIN & KEPSEK (Full Access ke seluruh kelas, atau spesifik kelas)
        if (in_array($userRole, ['admin', 'kepsek'])) {
            if (preg_match('/(kelas|di\s*kelas)\s*([0-9xvi]+\s*[a-z0-9]+)/i', $lower, $mKelas)) {
                $searchKelas = trim($mKelas[2]);
                $db->query("SELECT id, nama_kelas FROM kelas WHERE LOWER(nama_kelas) LIKE :k LIMIT 1");
                $db->bind(':k', '%' . $searchKelas . '%');
                $kRow = $db->single();
                if ($kRow) {
                    $filterKelasId = $kRow->id;
                    $namaKelasBinaan = $kRow->nama_kelas;
                }
            }
        }

        // 4. JIKA SPESIFIK MENANYAKAN GURU (Admin / Kepsek)
        $isTeacherFocus = preg_match('/\b(guru|pengajar|pendidik)\b/i', $lower) && !preg_match('/\b(siswa|murid|anak)\b/i', $lower);
        if ($isTeacherFocus && in_array($userRole, ['admin', 'kepsek'])) {
            $db->query("SELECT u.name, g.subject, ag.jam_datang, ag.status, ag.keterangan 
                        FROM absensi_guru ag 
                        JOIN guru g ON g.id = ag.guru_id 
                        JOIN users u ON u.id = g.user_id 
                        WHERE ag.tanggal = :tgl 
                        ORDER BY ag.jam_datang ASC");
            $db->bind(':tgl', $targetDate);
            $guruRows = $db->resultSet();

            $db->query("SELECT pi.jenis, pi.alasan, pi.status, u.name 
                        FROM pengajuan_izin pi 
                        JOIN users u ON u.id = pi.user_id 
                        WHERE u.role = 'guru' 
                          AND (:tgl BETWEEN pi.tanggal_mulai AND pi.tanggal_selesai OR pi.tanggal_mulai = :tgl)");
            $db->bind(':tgl', $targetDate);
            $guruIzinRows = $db->resultSet();

            $isIzinQuery = preg_match('/(izin|ijin|sakit|tidak\s*hadir|tidak\s*masuk)/i', $lower);
            if ($isIzinQuery) {
                $guruIzinList = [];
                foreach ($guruRows as $gr) {
                    if (in_array($gr->status, ['izin', 'sakit', 'cuti'])) {
                        $guruIzinList[] = "{$gr->name} — " . ucfirst($gr->status) . ($gr->keterangan ? ": {$gr->keterangan}" : "");
                    }
                }
                foreach ($guruIzinRows as $gi) {
                    $guruIzinList[] = "{$gi->name} — " . ucfirst($gi->jenis) . " [{$gi->status}]: {$gi->alasan}";
                }
                $guruIzinList = array_unique($guruIzinList);

                $respon = "**Data Guru Izin & Sakit — {$tglIndo}**\n\n";
                if (empty($guruIzinList)) {
                    $respon .= "Tidak ada guru yang tercatat izin atau sakit pada tanggal {$tglIndo}.";
                } else {
                    foreach ($guruIzinList as $i => $item) {
                        $respon .= ($i + 1) . ". **{$item}**\n";
                    }
                    $respon .= "\n_Total: " . count($guruIzinList) . " guru._";
                }
                return $respon;
            }

            $respon = "**Kehadiran Guru — {$tglIndo}** (Total: " . count($guruRows) . " Guru)\n\n";
            if (empty($guruRows)) {
                $respon .= "Belum ada catatan presensi guru pada tanggal {$tglIndo}.";
            } else {
                foreach ($guruRows as $i => $gr) {
                    $stBadge = match($gr->status) {
                        'hadir'     => '[Tepat Waktu]',
                        'terlambat' => '[Terlambat]',
                        'izin'      => '[Izin]',
                        'sakit'     => '[Sakit]',
                        default     => '[' . ucfirst($gr->status) . ']',
                    };
                    $jam = $gr->jam_datang ? " (" . substr($gr->jam_datang, 0, 5) . " WIB)" : "";
                    $mapel = ($gr->subject && $gr->subject !== '-') ? " — Mapel: {$gr->subject}" : "";
                    $respon .= ($i + 1) . ". **{$gr->name}**{$mapel} {$stBadge}{$jam}\n";
                }
            }
            return $respon;
        }

        // 5. QUERY DATA SISWA (ABSENSI, PENGAJUAN IZIN, PULANG CEPAT)
        $sqlSiswa = "SELECT a.status, a.keterangan, a.jam_datang, a.jam_pulang, u.name as nama_siswa, k.nama_kelas, s.id as siswa_id 
                     FROM absensi_siswa a 
                     JOIN siswa s ON s.id = a.siswa_id 
                     JOIN users u ON u.id = s.user_id 
                     LEFT JOIN kelas k ON k.id = s.kelas_id 
                     WHERE a.tanggal = :tgl";
        if ($filterKelasId) {
            $sqlSiswa .= " AND s.kelas_id = :kid";
        }
        $sqlSiswa .= " ORDER BY k.nama_kelas ASC, u.name ASC";
        $db->query($sqlSiswa);
        $db->bind(':tgl', $targetDate);
        if ($filterKelasId) $db->bind(':kid', $filterKelasId);
        $absenSiswaRows = $db->resultSet();

        $sqlIzin = "SELECT pi.jenis, pi.alasan, pi.status, u.name as nama_user, k.nama_kelas 
                    FROM pengajuan_izin pi 
                    JOIN users u ON u.id = pi.user_id 
                    LEFT JOIN siswa s ON s.user_id = u.id 
                    LEFT JOIN kelas k ON k.id = s.kelas_id 
                    WHERE (:tgl BETWEEN pi.tanggal_mulai AND pi.tanggal_selesai OR pi.tanggal_mulai = :tgl)
                      AND pi.status IN ('approved', 'pending')";
        if ($filterKelasId) {
            $sqlIzin .= " AND s.kelas_id = :kid";
        }
        $db->query($sqlIzin);
        $db->bind(':tgl', $targetDate);
        if ($filterKelasId) $db->bind(':kid', $filterKelasId);
        $izinRows = $db->resultSet();

        $sqlPc = "SELECT pc.alasan, pc.status, pc.jam_pengajuan, u.name as nama_siswa, k.nama_kelas 
                  FROM pengajuan_pulang_cepat pc 
                  JOIN siswa s ON s.id = pc.siswa_id 
                  JOIN users u ON u.id = s.user_id 
                  LEFT JOIN kelas k ON k.id = s.kelas_id 
                  WHERE pc.tanggal = :tgl";
        if ($filterKelasId) {
            $sqlPc .= " AND s.kelas_id = :kid";
        }
        $db->query($sqlPc);
        $db->bind(':tgl', $targetDate);
        if ($filterKelasId) $db->bind(':kid', $filterKelasId);
        $pcRows = $db->resultSet();

        // 6. REKAP & PENGELOMPOKAN
        $listHadir       = [];
        $listTerlambat   = [];
        $listIzin        = [];
        $listSakit       = [];
        $listPulangCepat = [];
        $listAlfa        = [];

        foreach ($absenSiswaRows as $r) {
            $nama = $r->nama_siswa;
            $kls  = $r->nama_kelas ?? '-';
            $jam  = $r->jam_datang ? substr($r->jam_datang, 0, 5) : '';
            $ket  = trim($r->keterangan ?? '');

            if ($r->status === 'hadir') {
                $listHadir[$nama] = "{$nama} ({$kls}) — Masuk {$jam} WIB";
            } elseif ($r->status === 'terlambat') {
                $listTerlambat[$nama] = "{$nama} ({$kls}) — Masuk {$jam} WIB [Terlambat]";
            } elseif ($r->status === 'izin') {
                $cleanKet = $this->cleanKeterangan($ket);
                $listIzin[$nama] = "{$nama} ({$kls})" . ($cleanKet ? ": {$cleanKet}" : "");
            } elseif ($r->status === 'sakit') {
                $cleanKet = $this->cleanKeterangan($ket);
                $listSakit[$nama] = "{$nama} ({$kls})" . ($cleanKet ? ": {$cleanKet}" : "");
            } elseif ($r->status === 'pulang_cepat') {
                $cleanKet = $this->cleanKeterangan($ket);
                $listPulangCepat[$nama] = "{$nama} ({$kls}) — Pulang Cepat" . ($cleanKet ? " ({$cleanKet})" : "");
            } elseif ($r->status === 'alfa') {
                $listAlfa[$nama] = "{$nama} ({$kls})";
            }
        }

        foreach ($izinRows as $iz) {
            $nama = $iz->nama_user;
            $kls  = $iz->nama_kelas ?? '-';
            $stBadge = ($iz->status === 'approved') ? 'Disetujui' : 'Pending';
            $alasan  = $iz->alasan ? ": {$iz->alasan}" : "";

            if (strtolower($iz->jenis) === 'sakit') {
                if (!isset($listSakit[$nama])) {
                    $listSakit[$nama] = "{$nama} ({$kls}) — Sakit [{$stBadge}]{$alasan}";
                }
            } else {
                if (!isset($listIzin[$nama])) {
                    $listIzin[$nama] = "{$nama} ({$kls}) — Izin [{$stBadge}]{$alasan}";
                }
            }
        }

        foreach ($pcRows as $pc) {
            $nama = $pc->nama_siswa;
            $kls  = $pc->nama_kelas ?? '-';
            $stBadge = strtoupper($pc->status) === 'APPROVED' ? 'Disetujui' : 'Pending';
            $alasan  = $pc->alasan ? ": {$pc->alasan}" : "";
            $jam     = $pc->jam_pengajuan ? " (Jam " . substr($pc->jam_pengajuan, 0, 5) . ")" : "";

            if (!isset($listPulangCepat[$nama])) {
                $listPulangCepat[$nama] = "{$nama} ({$kls}) — Pulang Cepat [{$stBadge}]{$jam}{$alasan}";
            }
        }

        $scopeKelas = $namaKelasBinaan ? " — Kelas {$namaKelasBinaan}" : "";

        // JIKA MENANYAKAN "SAKIT"
        $isSakitOnly = preg_match('/\bsakit\b/i', $lower) && !preg_match('/\b(izin|ijin|semua|rekap)\b/i', $lower);
        if ($isSakitOnly) {
            $sakitMerged = $listSakit;
            foreach ($listPulangCepat as $nama => $info) {
                if (stripos($info, 'sakit') !== false || stripos($info, 'flu') !== false || stripos($info, 'demam') !== false || stripos($info, 'pusing') !== false) {
                    if (!isset($sakitMerged[$nama])) {
                        $sakitMerged[$nama] = $info;
                    }
                }
            }

            $respon = "**Data Siswa Sakit — {$tglIndo}{$scopeKelas}**\n\n";
            if (empty($sakitMerged)) {
                $respon .= "Tidak ada siswa yang tercatat sakit pada tanggal {$tglIndo}.";
            } else {
                $i = 1;
                foreach ($sakitMerged as $item) {
                    $respon .= "{$i}. **{$item}**\n";
                    $i++;
                }
                $respon .= "\n_Total: " . count($sakitMerged) . " siswa._";
            }
            return $respon;
        }

        // JIKA MENANYAKAN "IZIN"
        $isIzinOnly = preg_match('/\b(izin|ijin)\b/i', $lower) && !preg_match('/\b(sakit|semua|rekap)\b/i', $lower);
        if ($isIzinOnly) {
            $respon = "**Data Siswa Izin — {$tglIndo}{$scopeKelas}**\n\n";
            if (empty($listIzin)) {
                $respon .= "Tidak ada siswa yang tercatat izin pada tanggal {$tglIndo}.";
            } else {
                $i = 1;
                foreach ($listIzin as $item) {
                    $respon .= "{$i}. **{$item}**\n";
                    $i++;
                }
                $respon .= "\n_Total: " . count($listIzin) . " siswa._";
            }
            return $respon;
        }

        // JIKA MENANYAKAN "PULANG CEPAT"
        $isPcOnly = preg_match('/pulang\s*cepat/i', $lower) && !preg_match('/\b(izin|ijin|sakit|rekap)\b/i', $lower);
        if ($isPcOnly) {
            $respon = "**Data Pulang Cepat — {$tglIndo}{$scopeKelas}**\n\n";
            if (empty($listPulangCepat)) {
                $respon .= "Tidak ada siswa yang pulang cepat pada tanggal {$tglIndo}.";
            } else {
                $i = 1;
                foreach ($listPulangCepat as $item) {
                    $respon .= "{$i}. **{$item}**\n";
                    $i++;
                }
                $respon .= "\n_Total: " . count($listPulangCepat) . " siswa._";
            }
            return $respon;
        }

        // JIKA MENANYAKAN "IZIN / SAKIT" / "IZIN ATAU SAKIT"
        $isIzinSakit = preg_match('/(izin.*sakit|sakit.*izin|izin\s*(\/|atau)\s*sakit|tidak\s*(masuk|hadir))/i', $lower) || 
                        (preg_match('/(izin|ijin|sakit)/i', $lower) && !preg_match('/(rekap|terlambat|telat|hadir)/i', $lower));
        if ($isIzinSakit) {
            $allPermits = [];
            foreach ($listSakit as $nama => $info) {
                $allPermits[] = "- **Sakit**: {$info}";
            }
            foreach ($listIzin as $nama => $info) {
                $allPermits[] = "- **Izin**: {$info}";
            }
            foreach ($listPulangCepat as $nama => $info) {
                $allPermits[] = "- **Pulang Cepat**: {$info}";
            }

            $respon = "**Data Izin & Sakit Siswa — {$tglIndo}{$scopeKelas}**\n\n";
            if (empty($allPermits)) {
                $respon .= "Tidak ada siswa yang tercatat izin, sakit, atau permohonan pulang cepat pada tanggal {$tglIndo}.";
            } else {
                $respon .= implode("\n", $allPermits) . "\n\n";
                $respon .= "_Total: " . count($allPermits) . " siswa tercatat izin/sakit/pulang cepat._";
            }
            return $respon;
        }

        // JIKA MENANYAKAN "TERLAMBAT" / "TELAT"
        if (preg_match('/(terlambat|telat)/i', $lower)) {
            $respon = "**Data Siswa Terlambat — {$tglIndo}{$scopeKelas}**\n\n";
            if (empty($listTerlambat)) {
                $respon .= "Tidak ada siswa yang tercatat terlambat pada tanggal {$tglIndo}.";
            } else {
                $i = 1;
                foreach ($listTerlambat as $item) {
                    $respon .= "{$i}. **{$item}**\n";
                    $i++;
                }
                $respon .= "\n_Total: " . count($listTerlambat) . " siswa terlambat._";
            }
            return $respon;
        }

        // JIKA MENANYAKAN "HADIR" / "MASUK"
        if (preg_match('/\b(hadir|masuk)\b/i', $lower) && !preg_match('/(tidak|rekap)/i', $lower)) {
            $allHadir = array_merge($listHadir, $listTerlambat);
            $totHadir = count($allHadir);
            $respon = "**Data Siswa Hadir — {$tglIndo}{$scopeKelas}** (Total: {$totHadir} Siswa)\n\n";
            if (empty($allHadir)) {
                $respon .= "Belum ada data siswa yang tercatat hadir pada tanggal {$tglIndo}.";
            } else {
                if ($totHadir > 25) {
                    $respon .= "- Tepat Waktu: " . count($listHadir) . " siswa\n";
                    $respon .= "- Terlambat  : " . count($listTerlambat) . " siswa\n\n";
                    $respon .= "_Daftar kehadiran lengkap dapat diunduh pada menu Laporan Rekap._";
                } else {
                    $i = 1;
                    foreach ($allHadir as $item) {
                        $respon .= "{$i}. {$item}\n";
                        $i++;
                    }
                }
            }
            return $respon;
        }

        // JIKA MENANYAKAN "ALFA" / "TIDAK MASUK" / "BOLOS"
        if (preg_match('/(alfa|tidak\s*(masuk|hadir)|bolos)/i', $lower)) {
            $totAlfa = count($listAlfa);
            $respon = "**Data Siswa Alfa (Tanpa Keterangan) — {$tglIndo}{$scopeKelas}** (Total: {$totAlfa} Siswa)\n\n";
            if ($totAlfa === 0) {
                $respon .= "Nihil. Tidak ada siswa yang tercatat alfa pada tanggal {$tglIndo}.";
            } elseif ($totAlfa > 15) {
                $alfaPerKelas = [];
                foreach ($absenSiswaRows as $r) {
                    if ($r->status === 'alfa') {
                        $kName = $r->nama_kelas ?? 'Tanpa Kelas';
                        $alfaPerKelas[$kName] = ($alfaPerKelas[$kName] ?? 0) + 1;
                    }
                }
                $respon .= "Total {$totAlfa} siswa tercatat alfa di sistem:\n";
                foreach ($alfaPerKelas as $kName => $cnt) {
                    $respon .= "- **{$kName}**: {$cnt} siswa\n";
                }
            } else {
                $i = 1;
                foreach ($listAlfa as $item) {
                    $respon .= "{$i}. {$item}\n";
                    $i++;
                }
            }
            return $respon;
        }

        // REKAPITULASI MENYELURUH UNTUK TANGGAL TERSEBUT
        $totHadir   = count($listHadir);
        $totTelat   = count($listTerlambat);
        $totIzin    = count($listIzin);
        $totSakit   = count($listSakit);
        $totPc      = count($listPulangCepat);
        $totAlfa    = count($listAlfa);
        $grandTotal = $totHadir + $totTelat + $totIzin + $totSakit + $totPc + $totAlfa;

        $respon = "**Rekapitulasi Kehadiran Siswa — {$tglIndo}{$scopeKelas}**\n\n"
                . "- **Hadir Tepat Waktu** : {$totHadir} siswa\n"
                . "- **Terlambat**         : {$totTelat} siswa\n"
                . "- **Izin**              : {$totIzin} siswa\n"
                . "- **Sakit**             : {$totSakit} siswa\n"
                . "- **Pulang Cepat**       : {$totPc} siswa\n"
                . "- **Alfa (Tanpa Ket.)** : {$totAlfa} siswa\n"
                . "- **Total Data**        : {$grandTotal} siswa\n";

        if ($totIzin > 0 || $totSakit > 0 || $totPc > 0) {
            $respon .= "\n**Rincian Izin / Sakit / Pulang Cepat:**\n";
            foreach ($listSakit as $s) $respon .= "- [Sakit] {$s}\n";
            foreach ($listIzin as $iz) $respon .= "- [Izin] {$iz}\n";
            foreach ($listPulangCepat as $pc) $respon .= "- [Pulang Cepat] {$pc}\n";
        }

        return $respon;
    }

    // =========================================================
    // BUILD CONTEXT DATABASE TERSINKRONISASI SESUAI RBAC
    // =========================================================
    private function buildRoleDatabaseContext($role, $userId, $db, $message = '') {
        $today    = date('Y-m-d');
        $bulanIni = date('Y-m');
        $hariIni  = $this->hariIndonesia(date('l'));
        $besok    = $this->hariIndonesia(date('l', strtotime('+1 day')));

        // Jadwal siswa hari ini dari tabel jadwal_siswa
        $db->query("SELECT jam_mulai_absensi, jam_datang, batas_terlambat, jam_pulang, status_aktif FROM jadwal_siswa WHERE hari = :h");
        $db->bind(':h', $hariIni);
        $schToday = $db->single();
        $schSiswaStr = ($schToday && $schToday->status_aktif) 
            ? "Jadwal Siswa Hari Ini ({$hariIni}): Mulai Absen " . substr($schToday->jam_mulai_absensi, 0, 5) . ", Masuk " . substr($schToday->jam_datang, 0, 5) . ", Batas Telat " . substr($schToday->batas_terlambat, 0, 5) . ", Pulang " . substr($schToday->jam_pulang, 0, 5) . "."
            : "Jadwal Siswa Hari Ini ({$hariIni}): Libur / Tidak Aktif.";

        // Ringkasan jadwal guru hari ini dari tabel jadwal_guru
        $db->query("SELECT MIN(jam_datang) as min_masuk, MAX(jam_pulang) as max_pulang, MIN(batas_terlambat) as min_telat, COUNT(DISTINCT guru_id) as total_guru 
                    FROM jadwal_guru WHERE hari = :h AND is_active = 1");
        $db->bind(':h', $hariIni);
        $schGuruToday = $db->single();
        $schGuruStr = ($schGuruToday && $schGuruToday->total_guru > 0)
            ? "Jadwal Mengajar Guru Hari Ini ({$hariIni}): Rentang Masuk " . substr($schGuruToday->min_masuk, 0, 5) . " s/d Pulang " . substr($schGuruToday->max_pulang, 0, 5) . " WIB (Batas Telat Sesi 1: " . substr($schGuruToday->min_telat, 0, 5) . ", Total " . $schGuruToday->total_guru . " Guru mengajar)."
            : "Jadwal Mengajar Guru Hari Ini ({$hariIni}): Tidak ada guru mengajar / libur.";

        $schStr = "{$schSiswaStr}\n{$schGuruStr}";

        $contextText     = "";
        $roleInstruction = "";

        // ── 1. SISWA (Hanya Data Pribadi Siswa) ─────────────────────────
        if ($role === 'siswa') {
            $db->query("SELECT s.id as siswa_id, s.nis, u.name, u.username, k.nama_kelas, COALESCE(s.rfid_uid, r.uid) as rfid_uid 
                        FROM siswa s 
                        JOIN users u ON u.id = s.user_id 
                        LEFT JOIN kelas k ON k.id = s.kelas_id 
                        LEFT JOIN rfid_cards r ON r.student_id = s.id AND r.status = 'active' 
                        WHERE s.user_id = :uid");
            $db->bind(':uid', $userId);
            $siswa = $db->single();

            if ($siswa) {
                // Kehadiran hari ini
                $db->query("SELECT jam_datang, jam_pulang, status, keterangan FROM absensi_siswa WHERE siswa_id = :sid AND tanggal = :tgl");
                $db->bind(':sid', $siswa->siswa_id);
                $db->bind(':tgl', $today);
                $absenHariIni = $db->single();
                $absenHariIniStr = $absenHariIni 
                    ? "Status: {$absenHariIni->status}, Masuk: " . ($absenHariIni->jam_datang ? substr($absenHariIni->jam_datang, 0, 5) : '-') . ", Pulang: " . ($absenHariIni->jam_pulang ? substr($absenHariIni->jam_pulang, 0, 5) : '-') . ($absenHariIni->keterangan ? " ({$absenHariIni->keterangan})" : '')
                    : "Belum tercatat absensi hari ini.";

                // Rekap bulan ini
                $db->query("SELECT SUM(status='hadir') as hadir, SUM(status='terlambat') as terlambat,
                                   SUM(status='izin') as izin, SUM(status='sakit') as sakit, SUM(status='alfa') as alfa 
                            FROM absensi_siswa WHERE siswa_id = :sid AND DATE_FORMAT(tanggal,'%Y-%m') = :bln");
                $db->bind(':sid', $siswa->siswa_id);
                $db->bind(':bln', $bulanIni);
                $rekap = $db->single();
                $rekapStr = "Hadir: " . ($rekap->hadir ?? 0) . ", Terlambat: " . ($rekap->terlambat ?? 0) . ", Izin: " . ($rekap->izin ?? 0) . ", Sakit: " . ($rekap->sakit ?? 0) . ", Alfa: " . ($rekap->alfa ?? 0);

                // Riwayat 5 hari terakhir
                $db->query("SELECT tanggal, jam_datang, status FROM absensi_siswa WHERE siswa_id = :sid ORDER BY tanggal DESC LIMIT 5");
                $db->bind(':sid', $siswa->siswa_id);
                $riwayat = $db->resultSet();
                $riwayatArr = [];
                foreach ($riwayat as $r) {
                    $riwayatArr[] = date('d/m', strtotime($r->tanggal)) . ": " . $r->status . " (" . ($r->jam_datang ? substr($r->jam_datang, 0, 5) : '-') . ")";
                }
                $riwayatStr = empty($riwayatArr) ? "Belum ada riwayat" : implode(', ', $riwayatArr);

                // Izin & Pulang Cepat
                $db->query("SELECT jenis, alasan, status FROM pengajuan_izin WHERE user_id = :uid ORDER BY id DESC LIMIT 2");
                $db->bind(':uid', $userId);
                $izinList = $db->resultSet();
                $izinArr = [];
                foreach ($izinList as $iz) { $izinArr[] = "{$iz->jenis} ({$iz->status}: {$iz->alasan})"; }
                $izinStr = empty($izinArr) ? "Tidak ada" : implode(', ', $izinArr);

                $db->query("SELECT tanggal, alasan, status FROM pengajuan_pulang_cepat WHERE siswa_id = :sid ORDER BY id DESC LIMIT 2");
                $db->bind(':sid', $siswa->siswa_id);
                $pcList = $db->resultSet();
                $pcArr = [];
                foreach ($pcList as $pc) { $pcArr[] = "{$pc->tanggal} ({$pc->status}: {$pc->alasan})"; }
                $pcStr = empty($pcArr) ? "Tidak ada" : implode(', ', $pcArr);

                $rfidStr = $siswa->rfid_uid ? "UID {$siswa->rfid_uid} (Aktif)" : "Belum terdaftar kartu RFID";

                $contextText = 
                    "=== DATA SINKRONISASI PRIBADI SISWA ===\n" .
                    "Nama Siswa: {$siswa->name}\n" .
                    "NIS: {$siswa->nis}\n" .
                    "Kelas: " . ($siswa->nama_kelas ?? '-') . "\n" .
                    "Status Kartu RFID: {$rfidStr}\n" .
                    "{$schStr}\n" .
                    "Kehadiran Hari Ini: {$absenHariIniStr}\n" .
                    "Rekap Absensi Bulan Ini: {$rekapStr}\n" .
                    "Riwayat 5 Hari Terakhir: {$riwayatStr}\n" .
                    "Pengajuan Izin Terbaru: {$izinStr}\n" .
                    "Pengajuan Pulang Cepat: {$pcStr}\n";

                $roleInstruction = 
                    "PERAN PENGGUNA SAAT INI: SISWA (Nama: {$siswa->name}, Kelas: " . ($siswa->nama_kelas ?? '-') . ").\n" .
                    "ATURAN BATASAN HAK AKSES SISWA (KETAT):\n" .
                    "- Anda HANYA BERHAK menjawab seputar data pribadi siswa ini (profil, kartu RFID, kehadiran, rekap, riwayat, izin, dan jadwal sekolah).\n" .
                    "- DILARANG KERAS memberikan data siswa lain (nama, kontak, absensi orang lain, atau daftar nama teman sekelas).\n" .
                    "- DILARANG KERAS memberikan data guru (NIP, nomor HP, jadwal guru, barcode) atau data perangkat IoT dan statistik admin.\n" .
                    "- Jika pengguna bertanya tentang data siswa lain atau guru, TOLAK DENGAN RAMAH dan katakan bahwa Anda hanya dapat menampilkan informasi pribadi milik akun yang sedang aktif demi menjaga privasi data sekolah.";
            }
        }

        // ── 2. GURU (Hanya Data Pribadi Guru) ──────────────────────────
        elseif ($role === 'guru') {
            $db->query("SELECT g.id as guru_id, g.nip, g.barcode_id, g.subject, u.name, u.username 
                        FROM guru g 
                        JOIN users u ON u.id = g.user_id 
                        WHERE g.user_id = :uid");
            $db->bind(':uid', $userId);
            $guru = $db->single();

            if ($guru) {
                // Jadwal guru
                $db->query("SELECT hari, jam_datang, batas_terlambat, jam_pulang FROM jadwal_guru WHERE guru_id = :gid AND is_active = 1 ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')");
                $db->bind(':gid', $guru->guru_id);
                $jadwalGuru = $db->resultSet();
                $jadwalArr = [];
                foreach ($jadwalGuru as $j) {
                    $jadwalArr[] = "{$j->hari}: Masuk " . substr($j->jam_datang, 0, 5) . " s/d Pulang " . substr($j->jam_pulang, 0, 5);
                }
                $jadwalStr = empty($jadwalArr) ? "Belum ada jadwal aktif" : implode('; ', $jadwalArr);

                // Absensi guru hari ini
                $db->query("SELECT jam_datang, jam_pulang, status, keterangan FROM absensi_guru WHERE guru_id = :gid AND tanggal = :tgl");
                $db->bind(':gid', $guru->guru_id);
                $db->bind(':tgl', $today);
                $absenHariIni = $db->single();
                $absenHariIniStr = $absenHariIni 
                    ? "Status: {$absenHariIni->status}, Jam Datang: " . ($absenHariIni->jam_datang ? substr($absenHariIni->jam_datang, 0, 5) : '-') . ", Jam Pulang: " . ($absenHariIni->jam_pulang ? substr($absenHariIni->jam_pulang, 0, 5) : '-')
                    : "Belum tercatat absensi hari ini.";

                // Rekap bulan ini
                $db->query("SELECT SUM(status='hadir') as hadir, SUM(status='terlambat') as terlambat,
                                   SUM(status='izin') as izin, SUM(status='sakit') as sakit, SUM(status='alfa') as alfa 
                            FROM absensi_guru WHERE guru_id = :gid AND DATE_FORMAT(tanggal,'%Y-%m') = :bln");
                $db->bind(':gid', $guru->guru_id);
                $db->bind(':bln', $bulanIni);
                $rekap = $db->single();
                $rekapStr = "Hadir: " . ($rekap->hadir ?? 0) . ", Terlambat: " . ($rekap->terlambat ?? 0) . ", Izin: " . ($rekap->izin ?? 0) . ", Sakit: " . ($rekap->sakit ?? 0) . ", Alfa: " . ($rekap->alfa ?? 0);

                // Pengajuan izin
                $db->query("SELECT jenis, alasan, status, tanggal_mulai, tanggal_selesai FROM pengajuan_izin WHERE user_id = :uid ORDER BY id DESC LIMIT 2");
                $db->bind(':uid', $userId);
                $izinList = $db->resultSet();
                $izinArr = [];
                foreach ($izinList as $iz) { $izinArr[] = "{$iz->jenis} ({$iz->status}: {$iz->alasan})"; }
                $izinStr = empty($izinArr) ? "Tidak ada" : implode(', ', $izinArr);

                $contextText = 
                    "=== DATA SINKRONISASI PRIBADI GURU ===\n" .
                    "Nama Guru: {$guru->name}\n" .
                    "NIP: {$guru->nip}\n" .
                    "Mata Pelajaran: " . ($guru->subject ?? 'Umum') . "\n" .
                    "Barcode ID: {$guru->barcode_id}\n" .
                    "Jadwal Mengajar Anda: {$jadwalStr}\n" .
                    "Absensi Anda Hari Ini: {$absenHariIniStr}\n" .
                    "Rekap Kehadiran Bulan Ini: {$rekapStr}\n" .
                    "Pengajuan Izin Terakhir: {$izinStr}\n" .
                    "{$schStr}\n";

                $roleInstruction = 
                    "PERAN PENGGUNA SAAT INI: GURU MATA PELAJARAN (Nama: {$guru->name}, Mapel: " . ($guru->subject ?? 'Umum') . ").\n" .
                    "ATURAN BATASAN HAK AKSES GURU (KETAT):\n" .
                    "- Anda HANYA BERHAK menjawab seputar jadwal mengajar guru ini, rekap absensi guru sendiri, barcode presensi, izin diri sendiri, dan jadwal/tata tertib sekolah.\n" .
                    "- DILARANG KERAS memberikan data siswa (daftar siswa, absensi siswa, izin siswa) atau data guru lain.\n" .
                    "- Jika pengguna bertanya mengenai data kehadiran siswa atau daftar siswa, TOLAK DENGAN HORMAT dan jelaskan bahwa data siswa hanya dapat diakses oleh Wali Kelas (untuk kelas binaannya), Kepala Sekolah, dan Administrator.";
            }
        }

        // ── 3. WALI KELAS (Guru Pribadi + Siswa Kelas Binaan) ────────────
        elseif ($role === 'wali_kelas') {
            $db->query("SELECT g.id as guru_id, g.nip, g.barcode_id, g.subject, u.name, u.username, k.id as kelas_id, k.nama_kelas 
                        FROM guru g 
                        JOIN users u ON u.id = g.user_id 
                        LEFT JOIN kelas k ON k.wali_kelas_id = u.id 
                        WHERE g.user_id = :uid");
            $db->bind(':uid', $userId);
            $wali = $db->single();

            if ($wali) {
                // Jadwal pribadi guru
                $db->query("SELECT hari, jam_datang, batas_terlambat, jam_pulang FROM jadwal_guru WHERE guru_id = :gid AND is_active = 1 ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')");
                $db->bind(':gid', $wali->guru_id);
                $jadwalGuru = $db->resultSet();
                $jadwalArr = [];
                foreach ($jadwalGuru as $j) {
                    $jadwalArr[] = "{$j->hari}: Masuk " . substr($j->jam_datang, 0, 5) . " - " . substr($j->jam_pulang, 0, 5);
                }
                $jadwalStr = empty($jadwalArr) ? "Belum ada jadwal aktif" : implode('; ', $jadwalArr);

                // Absensi pribadi wali kelas hari ini
                $db->query("SELECT jam_datang, jam_pulang, status FROM absensi_guru WHERE guru_id = :gid AND tanggal = :tgl");
                $db->bind(':gid', $wali->guru_id);
                $db->bind(':tgl', $today);
                $absenGuru = $db->single();
                $absenGuruStr = $absenGuru ? "Status: {$absenGuru->status}, Masuk: " . ($absenGuru->jam_datang ? substr($absenGuru->jam_datang, 0, 5) : '-') : "Belum absen hari ini";

                // DATA KELAS BINAAN
                $kelasInfo = "";
                $namaKelasBinaan = $wali->nama_kelas ?? 'Belum Ditugaskan';

                if ($wali->kelas_id) {
                    // Daftar siswa kelas
                    $db->query("SELECT u.name, s.nis, IF(r.id IS NOT NULL, 'RFID Aktif', 'Belum Ada Kartu') as status_rfid 
                                FROM siswa s 
                                JOIN users u ON u.id = s.user_id 
                                LEFT JOIN rfid_cards r ON r.student_id = s.id AND r.status = 'active' 
                                WHERE s.kelas_id = :kid ORDER BY u.name ASC");
                    $db->bind(':kid', $wali->kelas_id);
                    $siswaKelas = $db->resultSet();
                    $siswaKelasArr = [];
                    foreach ($siswaKelas as $sk) { $siswaKelasArr[] = "{$sk->name} (NIS: {$sk->nis}, {$sk->status_rfid})"; }
                    $totalSiswaKelas = count($siswaKelasArr);
                    $siswaKelasStr = empty($siswaKelasArr) ? "Belum ada siswa terdaftar" : implode(', ', $siswaKelasArr);

                    // Absensi siswa kelas hari ini
                    $db->query("SELECT u.name, a.jam_datang, a.status 
                                FROM absensi_siswa a 
                                JOIN siswa s ON s.id = a.siswa_id 
                                JOIN users u ON u.id = s.user_id 
                                WHERE s.kelas_id = :kid AND a.tanggal = :tgl 
                                ORDER BY a.jam_datang ASC");
                    $db->bind(':kid', $wali->kelas_id);
                    $db->bind(':tgl', $today);
                    $absenKelas = $db->resultSet();
                    $absenKelasArr = [];
                    foreach ($absenKelas as $ak) {
                        $absenKelasArr[] = "{$ak->name} (" . substr($ak->jam_datang, 0, 5) . ", {$ak->status})";
                    }
                    $absenKelasStr = empty($absenKelasArr) ? "Belum ada siswa yang absen hari ini" : implode(', ', $absenKelasArr);

                    // Izin pending siswa kelas
                    $db->query("SELECT u.name, pi.jenis, pi.alasan 
                                FROM pengajuan_izin pi 
                                JOIN users u ON u.id = pi.user_id 
                                JOIN siswa s ON s.user_id = u.id 
                                WHERE s.kelas_id = :kid AND pi.status = 'pending'");
                    $db->bind(':kid', $wali->kelas_id);
                    $izinPending = $db->resultSet();
                    $izinPendingArr = [];
                    foreach ($izinPending as $ip) { $izinPendingArr[] = "{$ip->name} ({$ip->jenis}: {$ip->alasan})"; }
                    $izinPendingStr = empty($izinPendingArr) ? "Tidak ada izin pending" : implode(', ', $izinPendingArr);

                    // Pulang cepat pending
                    $db->query("SELECT u.name, pc.alasan 
                                FROM pengajuan_pulang_cepat pc 
                                JOIN siswa s ON s.id = pc.siswa_id 
                                JOIN users u ON u.id = s.user_id 
                                WHERE s.kelas_id = :kid AND pc.status IN ('PENDING', 'DRAFT')");
                    $db->bind(':kid', $wali->kelas_id);
                    $pcPending = $db->resultSet();
                    $pcPendingArr = [];
                    foreach ($pcPending as $pp) { $pcPendingArr[] = "{$pp->name} ({$pp->alasan})"; }
                    $pcPendingStr = empty($pcPendingArr) ? "Tidak ada pulang cepat pending" : implode(', ', $pcPendingArr);

                    $kelasInfo = 
                        "KELAS BINAAN: {$namaKelasBinaan} (Total: {$totalSiswaKelas} Siswa)\n" .
                        "Daftar Siswa Kelas {$namaKelasBinaan}: {$siswaKelasStr}\n" .
                        "Kehadiran Siswa Kelas {$namaKelasBinaan} Hari Ini: {$absenKelasStr}\n" .
                        "Pengajuan Izin Pending Siswa: {$izinPendingStr}\n" .
                        "Pengajuan Pulang Cepat Pending Siswa: {$pcPendingStr}\n";
                } else {
                    $kelasInfo = "Status Wali Kelas: Belum ditugaskan ke kelas tertentu oleh admin.\n";
                }

                $contextText = 
                    "=== DATA SINKRONISASI WALI KELAS ===\n" .
                    "Nama: {$wali->name}\n" .
                    "NIP: {$wali->nip}\n" .
                    "Mapel: " . ($wali->subject ?? 'Umum') . "\n" .
                    "Jadwal Mengajar Anda: {$jadwalStr}\n" .
                    "Absensi Pribadi Hari Ini: {$absenGuruStr}\n" .
                    "{$schStr}\n" .
                    "=== DATA KELAS BINAAN WALI KELAS ===\n" .
                    $kelasInfo;

                $roleInstruction = 
                    "PERAN PENGGUNA SAAT INI: WALI KELAS untuk '{$namaKelasBinaan}' (Nama: {$wali->name}).\n" .
                    "ATURAN BATASAN HAK AKSES WALI KELAS:\n" .
                    "- Anda berhak mengakses data jadwal dan absensi pribadi Anda SEBAGAI GURU.\n" .
                    "- Anda BERHAK mengakses seluruh data siswa di KELAS BINAAN ANDA ('{$namaKelasBinaan}') seperti daftar siswa, absensi siswa kelas hari ini, dan pengajuan izin/pulang cepat siswa kelas binaan.\n" .
                    "- DILARANG KERAS memberikan data siswa dari KELAS LAIN yang bukan binaan Anda, data absensi guru lain, atau konfigurasi perangkat IoT admin.\n" .
                    "- Jika pengguna bertanya tentang siswa di kelas lain, tolak dengan sopan bahwa pengguna hanya berwenang untuk kelas binaannya sendiri ({$namaKelasBinaan}).";
            }
        }

        // ── 4. ADMIN & KEPSEK (Superuser Full Sync Database) ───────────
        elseif ($role === 'admin' || $role === 'kepsek') {
            $actorTitle = ($role === 'admin') ? 'ADMINISTRATOR' : 'KEPALA SEKOLAH';

            // Master totals
            $db->query("SELECT COUNT(*) as total FROM siswa"); $totSiswa = $db->single()->total ?? 0;
            $db->query("SELECT COUNT(*) as total FROM guru");  $totGuru  = $db->single()->total ?? 0;
            $db->query("SELECT COUNT(*) as total FROM kelas"); $totKelas = $db->single()->total ?? 0;

            // Kelas & Wali Kelas
            $db->query("SELECT k.nama_kelas, u.name as wali_nama FROM kelas k LEFT JOIN users u ON u.id = k.wali_kelas_id ORDER BY k.nama_kelas ASC");
            $kelasList = $db->resultSet();
            $kelasArr = [];
            foreach ($kelasList as $k) {
                $kelasArr[] = "{$k->nama_kelas} (Wali: " . ($k->wali_nama ?? 'Belum ada') . ")";
            }
            $kelasStr = implode(', ', $kelasArr);

            // Guru List (Format Padat & Hemat Token)
            $db->query("SELECT u.name, g.nip, g.subject FROM guru g JOIN users u ON u.id = g.user_id ORDER BY u.name ASC LIMIT 100");
            $guruList = $db->resultSet();
            $guruArr = [];
            foreach ($guruList as $g) {
                $info = [];
                if (!empty($g->subject) && $g->subject !== '-') $info[] = $g->subject;
                if (!empty($g->nip) && $g->nip !== '-') $info[] = "NIP: {$g->nip}";
                $infoStr = !empty($info) ? " (" . implode(', ', $info) . ")" : "";
                $guruArr[] = "{$g->name}{$infoStr}";
            }
            $guruStr = implode('; ', $guruArr);

            // Guru Mengajar Hari Ini & Besok
            $db->query("SELECT DISTINCT u.name FROM jadwal_guru j JOIN guru g ON g.id = j.guru_id JOIN users u ON u.id = g.user_id WHERE j.hari = :h AND j.is_active = 1");
            $db->bind(':h', $hariIni);
            $guruHariIni = $db->resultSet();
            $guruTodayArr = [];
            foreach ($guruHariIni as $gh) { $guruTodayArr[] = $gh->name; }
            $guruTodayStr = empty($guruTodayArr) ? "Tidak ada jadwal guru hari ini" : implode(', ', $guruTodayArr);

            // Jadwal Mengajar Seluruh Guru (Format Padat & Efisien Token)
            $db->query("SELECT u.name, j.hari, j.jam_datang, j.jam_pulang 
                        FROM jadwal_guru j 
                        JOIN guru g ON g.id = j.guru_id 
                        JOIN users u ON u.id = g.user_id 
                        WHERE j.is_active = 1 
                        ORDER BY u.name ASC, FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')");
            $allJadwal = $db->resultSet();
            $jadwalGrouped = [];
            foreach ($allJadwal as $row) {
                $hShort = match($row->hari) {
                    'Senin' => 'Sen', 'Selasa' => 'Sel', 'Rabu' => 'Rab',
                    'Kamis' => 'Kam', 'Jumat' => 'Jum', 'Sabtu' => 'Sab', default => $row->hari
                };
                $jadwalGrouped[$row->name][] = "{$hShort} " . substr($row->jam_datang, 0, 5) . "-" . substr($row->jam_pulang, 0, 5);
            }
            $jadwalGuruArr = [];
            foreach ($jadwalGrouped as $tName => $tTimes) {
                $jadwalGuruArr[] = "{$tName} (" . implode(', ', $tTimes) . ")";
            }
            $jadwalGuruFullStr = empty($jadwalGuruArr) ? "Belum ada jadwal mengajar aktif" : implode('; ', $jadwalGuruArr);

            // Kehadiran Guru Hari Ini
            $db->query("SELECT u.name, ag.jam_datang, ag.status FROM absensi_guru ag JOIN guru g ON g.id = ag.guru_id JOIN users u ON u.id = g.user_id WHERE ag.tanggal = :tgl ORDER BY ag.jam_datang ASC");
            $db->bind(':tgl', $today);
            $absenGuruList = $db->resultSet();
            $absenGuruArr = [];
            foreach ($absenGuruList as $ag) { $absenGuruArr[] = "{$ag->name} (" . substr($ag->jam_datang, 0, 5) . ", {$ag->status})"; }
            $absenGuruStr = empty($absenGuruArr) ? "Belum ada guru yang absen hari ini" : implode(', ', $absenGuruArr);

            // Statistik Kehadiran Siswa Hari Ini (Ringkas & Agregat - Hemat Ribuan Token)
            $db->query("SELECT 
                            COUNT(*) as total_masuk,
                            SUM(status='hadir') as hadir,
                            SUM(status='terlambat') as telat,
                            SUM(status='izin') as izin,
                            SUM(status='sakit') as sakit,
                            SUM(status='alfa') as alfa 
                        FROM absensi_siswa WHERE tanggal = :tgl");
            $db->bind(':tgl', $today);
            $statSiswa = $db->single();
            $statSiswaStr = "Total tercatat masuk: " . ($statSiswa->total_masuk ?? 0) . " (Tepat: " . ($statSiswa->hadir ?? 0) . ", Telat: " . ($statSiswa->telat ?? 0) . ", Izin: " . ($statSiswa->izin ?? 0) . ", Sakit: " . ($statSiswa->sakit ?? 0) . ", Alfa: " . ($statSiswa->alfa ?? 0) . ")";

            // Siswa Terlambat Hari Ini (Penting untuk Kepsek/Admin)
            $db->query("SELECT u.name, k.nama_kelas, a.jam_datang 
                        FROM absensi_siswa a 
                        JOIN siswa s ON s.id = a.siswa_id 
                        JOIN users u ON u.id = s.user_id 
                        LEFT JOIN kelas k ON k.id = s.kelas_id 
                        WHERE a.tanggal = :tgl AND a.status = 'terlambat' 
                        ORDER BY a.jam_datang ASC LIMIT 20");
            $db->bind(':tgl', $today);
            $telatList = $db->resultSet();
            $telatArr = [];
            foreach ($telatList as $tl) {
                $telatArr[] = "{$tl->name} ({$tl->nama_kelas}, " . substr($tl->jam_datang, 0, 5) . ")";
            }
            $telatStr = empty($telatArr) ? "Tidak ada siswa terlambat hari ini" : implode(', ', $telatArr);

            // IoT Devices
            $db->query("SELECT device_name, ip_address, wifi_ssid, status, last_seen FROM iot_devices ORDER BY id ASC");
            $iotList = $db->resultSet();
            $iotArr = [];
            foreach ($iotList as $d) {
                $isOnline = ($d->last_seen && (time() - strtotime($d->last_seen) <= 120));
                $st = $isOnline ? "Online" : "Offline";
                $iotArr[] = "{$d->device_name} (IP: {$d->ip_address}, WiFi: {$d->wifi_ssid}, Status: {$st})";
            }
            $iotStr = empty($iotArr) ? "Belum ada perangkat IoT" : implode('; ', $iotArr);

            // Permohonan izin & pulang cepat pending
            $db->query("SELECT u.name, u.role, pi.jenis, pi.alasan FROM pengajuan_izin pi JOIN users u ON u.id = pi.user_id WHERE pi.status = 'pending' LIMIT 15");
            $pIzinList = $db->resultSet();
            $pIzinArr = [];
            foreach ($pIzinList as $pi) { $pIzinArr[] = "{$pi->name} ({$pi->role}, {$pi->jenis}: {$pi->alasan})"; }
            $pIzinStr = empty($pIzinArr) ? "Tidak ada izin pending" : implode('; ', $pIzinArr);

            $db->query("SELECT u.name, pc.alasan FROM pengajuan_pulang_cepat pc JOIN siswa s ON s.id = pc.siswa_id JOIN users u ON u.id = s.user_id WHERE pc.status IN ('PENDING', 'DRAFT') LIMIT 15");
            $pPcList = $db->resultSet();
            $pPcArr = [];
            foreach ($pPcList as $pp) { $pPcArr[] = "{$pp->name} ({$pp->alasan})"; }
            $pPcStr = empty($pPcArr) ? "Tidak ada pulang cepat pending" : implode('; ', $pPcArr);

            $contextText = 
                "=== DATA SINKRONISASI DATABASE MENYELURUH (SUPERUSER AKSES PENUH) ===\n" .
                "Statistik Utama: {$totSiswa} Siswa, {$totGuru} Guru, {$totKelas} Kelas.\n" .
                "Distribusi Kelas & Siswa: {$kelasStr}\n" .
                "Daftar Guru ({$totGuru}): {$guruStr}\n" .
                "Guru Mengajar Hari Ini ({$hariIni}): {$guruTodayStr}\n" .
                "Jadwal Mengajar Seluruh Guru: {$jadwalGuruFullStr}\n" .
                "Kehadiran Guru Hari Ini: {$absenGuruStr}\n" .
                "Statistik Kehadiran Siswa Hari Ini: {$statSiswaStr}\n" .
                "Siswa Terlambat Hari Ini: {$telatStr}\n" .
                "Perangkat IoT Scanner: {$iotStr}\n" .
                "Permohonan Izin Pending: {$pIzinStr}\n" .
                "Permohonan Pulang Cepat Pending: {$pPcStr}\n" .
                "{$schStr}\n";

            // Sinkronisasi data presensi tanggal spesifik jika user bertanya tanggal lain
            $targetDate = $this->parseDateFromMessage($message);
            if ($targetDate && $targetDate !== $today) {
                $tglIndo = $this->formatTanggalIndo($targetDate);
                $db->query("SELECT a.status, a.keterangan, u.name as nama_siswa, k.nama_kelas 
                            FROM absensi_siswa a 
                            JOIN siswa s ON s.id = a.siswa_id 
                            JOIN users u ON u.id = s.user_id 
                            LEFT JOIN kelas k ON k.id = s.kelas_id 
                            WHERE a.tanggal = :tgl");
                $db->bind(':tgl', $targetDate);
                $tAbsen = $db->resultSet();

                $db->query("SELECT pi.jenis, pi.alasan, pi.status, u.name, k.nama_kelas 
                            FROM pengajuan_izin pi 
                            JOIN users u ON u.id = pi.user_id 
                            LEFT JOIN siswa s ON s.user_id = u.id 
                            LEFT JOIN kelas k ON k.id = s.kelas_id 
                            WHERE (:tgl BETWEEN pi.tanggal_mulai AND pi.tanggal_selesai OR pi.tanggal_mulai = :tgl)
                              AND pi.status IN ('approved', 'pending')");
                $db->bind(':tgl', $targetDate);
                $tIzin = $db->resultSet();

                $db->query("SELECT pc.alasan, pc.status, u.name as nama_siswa, k.nama_kelas 
                            FROM pengajuan_pulang_cepat pc 
                            JOIN siswa s ON s.id = pc.siswa_id 
                            JOIN users u ON u.id = s.user_id 
                            LEFT JOIN kelas k ON k.id = s.kelas_id 
                            WHERE pc.tanggal = :tgl");
                $db->bind(':tgl', $targetDate);
                $tPc = $db->resultSet();

                $totH = 0; $totT = 0; $totI = 0; $totS = 0; $totP = 0; $totA = 0;
                $izinSakitArr = [];
                foreach ($tAbsen as $ta) {
                    if ($ta->status === 'hadir') $totH++;
                    elseif ($ta->status === 'terlambat') $totT++;
                    elseif ($ta->status === 'izin') { $totI++; $izinSakitArr[] = "{$ta->nama_siswa} ({$ta->nama_kelas}) - Izin: " . $this->cleanKeterangan($ta->keterangan); }
                    elseif ($ta->status === 'sakit') { $totS++; $izinSakitArr[] = "{$ta->nama_siswa} ({$ta->nama_kelas}) - Sakit: " . $this->cleanKeterangan($ta->keterangan); }
                    elseif ($ta->status === 'pulang_cepat') { $totP++; $izinSakitArr[] = "{$ta->nama_siswa} ({$ta->nama_kelas}) - Pulang Cepat: " . $this->cleanKeterangan($ta->keterangan); }
                    elseif ($ta->status === 'alfa') $totA++;
                }
                foreach ($tIzin as $ti) {
                    $izinSakitArr[] = "{$ti->name} ({$ti->nama_kelas}) - " . ucfirst($ti->jenis) . " [{$ti->status}]: {$ti->alasan}";
                }
                foreach ($tPc as $tpc) {
                    if ($totP == 0) {
                        $izinSakitArr[] = "{$tpc->nama_siswa} ({$tpc->nama_kelas}) - Pulang Cepat [{$tpc->status}]: {$tpc->alasan}";
                    }
                }
                $izinSakitStr = empty($izinSakitArr) ? "Tidak ada siswa yang izin/sakit/pulang cepat" : implode('; ', array_unique($izinSakitArr));

                $contextText .= "\n=== DATA SINKRONISASI TANGGAL SPESIFIK: {$targetDate} ({$tglIndo}) ===\n" .
                                "Statistik Presensi Siswa: Hadir: {$totH}, Terlambat: {$totT}, Izin: {$totI}, Sakit: {$totS}, Pulang Cepat: {$totP}, Alfa: {$totA}.\n" .
                                "Daftar Siswa Izin/Sakit/Pulang Cepat: {$izinSakitStr}\n";
            }

            $roleInstruction = 
                "PERAN PENGGUNA SAAT INI: {$actorTitle} (Superuser Akses Penuh).\n" .
                "HAK AKSES:\n" .
                "- Pengguna memiliki akses PENUH tanpa batasan ke seluruh data sekolah: seluruh siswa, guru, kelas, rekap absensi, izin, dan perangkat IoT.\n" .
                "- Jawablah setiap pertanyaan pengguna secara lengkap, terstruktur, faktual, dan akurat berdasarkan data sinkronisasi di atas.";
        }

        return [
            'context_text'     => $contextText,
            'role_instruction' => $roleInstruction,
        ];
    }

    // =========================================================
    // GEMINI API — DENGAN RBAC DATABASE SYNC
    // =========================================================
        // =========================================================
    // MULTI-PROVIDER AI ENGINE (GROQ, xAI GROK & GOOGLE GEMINI)
    // =========================================================
    private function callGeminiApi($message, $role, $userId) {
        return $this->callAiApi($message, $role, $userId);
    }

    private function callAiApi($message, $role, $userId) {
        $aiConf   = class_exists('AppSetting') ? AppSetting::getAiConfig() : ['api_key' => '', 'model' => 'llama-3.3-70b-versatile', 'provider' => 'groq'];
        $apiKey   = !empty($aiConf['api_key']) ? $aiConf['api_key'] : (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '');
        $provider = !empty($aiConf['provider']) ? $aiConf['provider'] : 'groq';
        $model    = !empty($aiConf['model']) ? $aiConf['model'] : ($provider === 'gemini' ? 'gemini-2.5-flash' : 'llama-3.3-70b-versatile');

        // Fallback jika API Key belum diisi
        if (empty($apiKey)) {
            $localFallback = $this->handleLocalDatabaseQuery($message, $userId, $role);
            if ($localFallback !== false) {
                return $localFallback;
            }
            return "Halo! Kunci API AI (Groq / Grok / Gemini) belum dikonfigurasi oleh Administrator pada Pengaturan AI.\n\nNamun saya tetap dapat membantu Anda melihat data kehadiran hari ini, daftar guru/siswa, jadwal sekolah, dan panduan fitur sistem absensi. Silakan tanyakan data yang ingin Anda ketahui!";
        }

        $db = new Database();

        // Bangun konteks database tersinkronisasi sesuai RBAC
        $contextData     = $this->buildRoleDatabaseContext($role, $userId, $db, $message);
        $contextText     = $contextData['context_text'];
        $roleInstruction = $contextData['role_instruction'];

        $systemInstruction =
            "Anda adalah Asisten AI Resmi Sistem Absensi SMK NU Wargabinangun.\n\n" .
            "INFORMASI SISTEM & AKTOR RESMI:\n" .
            "Sistem Presensi SMK NU Wargabinangun mengelola absensi RFID siswa, QR Code guru, presensi mandiri GPS/kamera PKL, dan notifikasi Telegram.\n" .
            "PENEGAKAN HAK AKSES PERAN PENGGUNA (RBAC):\n" .
            "{$roleInstruction}\n\n" .
            "ATURAN RESPON:\n" .
            "1. Jawablah secara langsung, sopan, ringkas, faktual, dan akurat berdasarkan DATA SINKRONISASI DATABASE di bawah.\n" .
            "2. Jangan gunakan format bertele-tele atau salam pembuka berlebihan.\n" .
            "3. Jika ditanya hitungan, matematika, pengetahuan umum atau teknologi di luar database, jawablah secara edukatif dan jelas.\n" .
            "4. DILARANG membuatkan kode program (coding/programming) atau mengerjakan soal ujian/PR tugas sekolah siswa.\n\n" .
            "DATA SINKRONISASI DATABASE SEKOLAH:\n" .
            "{$contextText}\n";

        // ── A. PROVIDER: GROQ atau xAI GROK (Format OpenAI Compatible) ──
        if ($provider === 'groq' || $provider === 'grok') {
            $endpoint = ($provider === 'groq') 
                ? 'https://api.groq.com/openai/v1/chat/completions' 
                : 'https://api.x.ai/v1/chat/completions';

            $candidateModels = ($provider === 'groq')
                ? array_values(array_unique([$model, 'llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'mixtral-8x7b-32768']))
                : array_values(array_unique([$model, 'grok-2-latest', 'grok-beta']));

            foreach ($candidateModels as $cModel) {
                $payload = [
                    'model'       => $cModel,
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemInstruction],
                        ['role' => 'user',   'content' => $message]
                    ],
                    'temperature' => 0.4,
                    'max_tokens'  => 1200
                ];

                $ch = curl_init($endpoint);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_HTTPHEADER     => [
                        'Authorization: Bearer ' . trim($apiKey),
                        'Content-Type: application/json'
                    ],
                    CURLOPT_POSTFIELDS     => json_encode($payload),
                    CURLOPT_TIMEOUT        => 20,
                    CURLOPT_CONNECTTIMEOUT => 6,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);

                $response  = curl_exec($ch);
                $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if (!$curlError && $httpCode === 200) {
                    $jsonRes = json_decode($response, true);
                    $reply = $jsonRes['choices'][0]['message']['content'] ?? null;
                    if (!empty($reply)) {
                        return trim($reply);
                    }
                } else {
                    error_log("[Chatbot {$provider}] Model {$cModel} HTTP {$httpCode}: " . substr($response, 0, 250));
                }
            }
        }

        // ── B. PROVIDER: GOOGLE GEMINI ──
        if ($provider === 'gemini' || empty($provider)) {
            $candidateModels = array_values(array_unique([$model, 'gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-flash-latest']));
            $payload = [
                'system_instruction' => ['parts' => [['text' => $systemInstruction]]],
                'contents'           => [['role' => 'user', 'parts' => [['text' => $message]]]],
                'generationConfig'   => [
                    'temperature'     => 0.4,
                    'maxOutputTokens' => 1500,
                ],
            ];

            foreach ($candidateModels as $cModel) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$cModel}:generateContent?key={$apiKey}";
                $ch  = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                    CURLOPT_POSTFIELDS     => json_encode($payload),
                    CURLOPT_TIMEOUT        => 20,
                    CURLOPT_CONNECTTIMEOUT => 6,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);

                $response  = curl_exec($ch);
                $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if (!$curlError && $httpCode === 200) {
                    $data = json_decode($response, true);
                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($text !== null) {
                        return trim($text);
                    }
                }
            }
        }

        // Jika semua remote provider gagal, coba jawab dari database lokal
        $localFallback = $this->handleLocalDatabaseQuery($message, $userId, $role);
        if ($localFallback !== false) {
            return $localFallback;
        }

        return "Maaf, koneksi ke server AI (" . strtoupper($provider) . ") sedang mengalami antrean padat. Anda tetap dapat menanyakan data kehadiran, absensi PKL, dan informasi sekolah lainnya!";
    }

    // =========================================================
    // HELPERS
    // =========================================================
    private function hariIndonesia($dayName) {
        return [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ][$dayName] ?? $dayName;
    }

    private function namaBulan($n) {
        return [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'][(int)$n] ?? '';
    }

    private function cleanKeterangan($ket) {
        if (empty($ket)) return '';
        $ket = preg_replace('/\s*\[[^\]]+\]/i', '', $ket);
        $ket = preg_replace('/^Pulang Cepat di-ACC:\s*/i', '', $ket);
        return trim($ket);
    }

    private function formatTanggalIndo($dateStr) {
        if (empty($dateStr)) return '-';
        $time = strtotime($dateStr);
        $hari = $this->hariIndonesia(date('l', $time));
        $tgl  = date('j', $time);
        $bln  = $this->namaBulan(date('n', $time));
        $thn  = date('Y', $time);
        return "{$hari}, {$tgl} {$bln} {$thn}";
    }

    private function parseDateFromMessage($message) {
        $lower = strtolower(trim($message));
        $currentYear = date('Y');
        $currentMonth = date('m');

        // 1. Relatif
        if (preg_match('/\b(hari\s*ini|sekarang)\b/i', $lower)) {
            return date('Y-m-d');
        }
        if (preg_match('/\b(kemarin|kemaren)\b/i', $lower)) {
            return date('Y-m-d', strtotime('-1 day'));
        }
        if (preg_match('/\bbesok\b/i', $lower)) {
            return date('Y-m-d', strtotime('+1 day'));
        }
        if (preg_match('/\b([0-9]+)\s*hari\s*(lalu|yang\s*lalu)\b/i', $lower, $m)) {
            $days = (int)$m[1];
            return date('Y-m-d', strtotime("-{$days} days"));
        }

        // 2. Format YYYY-MM-DD
        if (preg_match('/\b(20\d{2})[-\/.](0?[1-9]|1[0-2])[-\/.](0?[1-9]|[12]\d|3[01])\b/', $lower, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        // 3. Format DD-MM-YYYY atau DD/MM/YYYY
        if (preg_match('/\b(0?[1-9]|[12]\d|3[01])[-\/.](0?[1-9]|1[0-2])[-\/.](20\d{2})\b/', $lower, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // 4. Format DD-MM atau DD/MM
        if (preg_match('/\b(0?[1-9]|[12]\d|3[01])[-\/.](0?[1-9]|1[0-2])\b/', $lower, $m)) {
            return sprintf('%04d-%02d-%02d', $currentYear, $m[2], $m[1]);
        }

        // 5. Format teks tanggal & bulan Indo
        $bulanMap = [
            'januari' => '01', 'jan' => '01',
            'februari' => '02', 'feb' => '02',
            'maret' => '03', 'mar' => '03',
            'april' => '04', 'apr' => '04',
            'mei' => '05',
            'juni' => '06', 'jun' => '06',
            'juli' => '07', 'jul' => '07',
            'agustus' => '08', 'agu' => '08', 'agt' => '08',
            'september' => '09', 'sep' => '09',
            'oktober' => '10', 'okt' => '10',
            'november' => '11', 'nov' => '11',
            'desember' => '12', 'des' => '12',
        ];
        $bulanRegex = implode('|', array_keys($bulanMap));
        
        if (preg_match('/(?:tanggal|tgl)?\s*([0-9]{1,2})\s*(?:bulan)?\s*(' . $bulanRegex . ')(?:\s*(20\d{2}))?/i', $lower, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = $bulanMap[strtolower($m[2])];
            $year = !empty($m[3]) ? $m[3] : $currentYear;
            return "{$year}-{$month}-{$day}";
        }

        if (preg_match('/(?:tanggal|tgl)\s*([0-9]{1,2})\b/i', $lower, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            return "{$currentYear}-{$currentMonth}-{$day}";
        }

        return null;
    }
}
