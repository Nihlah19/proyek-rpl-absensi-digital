<?php
class Flasher {
    public static function setFlash($pesan, $aksi, $tipe) {
        $_SESSION['flash'] = [
            'pesan' => $pesan,
            'aksi'  => $aksi,
            'tipe'  => $tipe
        ];
    }

    public static function flash() {
        if (!isset($_SESSION['flash'])) return;

        $tipe  = $_SESSION['flash']['tipe']  ?? 'info';
        $pesan = $_SESSION['flash']['pesan'] ?? '';
        $aksi  = $_SESSION['flash']['aksi']  ?? '';

        $config = [
            'success' => [
                'bg'     => '#dcfce7',
                'border' => '#86efac',
                'text'   => '#15803d',
                'icon'   => 'fa-circle-check',
                'sound'  => 'success',
                'title'  => 'Berhasil!',
            ],
            'danger' => [
                'bg'     => '#fee2e2',
                'border' => '#fca5a5',
                'text'   => '#b91c1c',
                'icon'   => 'fa-circle-xmark',
                'sound'  => 'error',
                'title'  => 'Gagal!',
            ],
            'warning' => [
                'bg'     => '#fef9c3',
                'border' => '#fde047',
                'text'   => '#a16207',
                'icon'   => 'fa-triangle-exclamation',
                'sound'  => 'warning',
                'title'  => 'Perhatian!',
            ],
            'info' => [
                'bg'     => '#dbeafe',
                'border' => '#93c5fd',
                'text'   => '#1d4ed8',
                'icon'   => 'fa-circle-info',
                'sound'  => 'info',
                'title'  => 'Informasi',
            ],
        ];

        $c     = $config[$tipe] ?? $config['info'];
        $msgId = 'flash_' . uniqid();
        $sound = $c['sound'];

        // Generate base64 audio (Tring / Beep) on the fly
        $rate = 22050; $len = 0.3; $data = '';
        if ($sound === 'success') {
            for($i=0; $i<$rate*$len; $i++) {
                $data .= pack('v', sin($i*2*M_PI*880/$rate)*10000 * exp(-$i/($rate*0.1)));
            }
        } elseif ($sound === 'error') {
            $len = 0.4;
            for($i=0; $i<$rate*$len; $i++) {
                $data .= pack('v', (sin($i*2*M_PI*200/$rate)>0?1:-1)*8000 * exp(-$i/($rate*0.2)));
            }
        } else {
            $len = 0.15;
            for($i=0; $i<$rate*$len; $i++) {
                $data .= pack('v', sin($i*2*M_PI*600/$rate)*8000 * exp(-$i/($rate*0.05)));
            }
        }
        $size = strlen($data);
        $wav = pack('A4VA4A4VvvVVvvA4V','RIFF',36+$size,'WAVE','fmt ',16,1,1,$rate,$rate*2,2,16,'data',$size).$data;
        $base64Audio = 'data:audio/wav;base64,' . base64_encode($wav);

        echo <<<HTML
<div id="{$msgId}" style="
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 320px;
    max-width: 420px;
    background: {$c['bg']};
    border: 1.5px solid {$c['border']};
    border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.07);
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 18px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    animation: flashSlideIn 0.4s cubic-bezier(0.34,1.56,0.64,1) both;
    transition: opacity 0.4s, transform 0.4s;
">
    <div style="
        width: 40px; height: 40px; border-radius: 50%;
        background: {$c['text']}20;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    ">
        <i class="fa-solid {$c['icon']}" style="color:{$c['text']}; font-size:1.1rem;"></i>
    </div>
    <div style="flex: 1; min-width: 0;">
        <p style="font-weight:700; color:{$c['text']}; font-size:0.875rem; margin:0 0 2px 0;">{$c['title']}</p>
        <p style="color:{$c['text']}cc; font-size:0.8rem; margin:0; word-break:break-word;">
            <strong>{$pesan}</strong> {$aksi}
        </p>
    </div>
    <button onclick="flasherClose('{$msgId}')" style="
        background: none; border: none; cursor: pointer;
        color: {$c['text']}; opacity: 0.6; padding: 2px;
        line-height: 1; flex-shrink: 0; font-size: 1rem;
        transition: opacity 0.2s;
    " onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.6" aria-label="Tutup">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>

<style>
@keyframes flashSlideIn {
    from { opacity:0; transform: translateX(60px) scale(0.95); }
    to   { opacity:1; transform: translateX(0) scale(1); }
}
@keyframes flashSlideOut {
    from { opacity:1; transform: translateX(0) scale(1); }
    to   { opacity:0; transform: translateX(60px) scale(0.9); }
}
</style>

<script>
(function() {
    // Gunakan tag audio, mainkan sekali tanpa looping
    var audio = new Audio("{$base64Audio}");
    audio.play().catch(function() {
        document.addEventListener('click', function handler() {
            audio.play().catch(function(){});
        }, {once: true});
    });

    // Auto close setelah 5 detik
    setTimeout(function() { flasherClose('{$msgId}'); }, 5000);
})();

function flasherClose(id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.style.animation = 'flashSlideOut 0.35s cubic-bezier(0.36,0.07,0.19,0.97) both';
    setTimeout(function() { if (el && el.parentNode) el.parentNode.removeChild(el); }, 350);
}
</script>
HTML;

        unset($_SESSION['flash']);
    }
}