<?php
/**
 * Helper: Badge Status
 * 
 * Template badge konsisten untuk seluruh aplikasi (Light & Dark Mode ready).
 * Panduan warna:
 *   - emerald  → hadir, disetujui, aktif
 *   - amber    → terlambat, menunggu, pulang cepat
 *   - sky      → izin, info umum
 *   - purple   → sakit
 *   - rose     → alfa, ditolak, offline
 *   - indigo   → pkl
 *   - slate    → nonaktif, netral, pending
 */

class BadgeHelper
{
    /**
     * Mapping status → warna badge (Light + Dark mode dual theme).
     */
    private static array $map = [
        // Absensi siswa & guru
        'hadir'        => [
            'bg'     => 'bg-emerald-50 dark:bg-emerald-500/15',
            'text'   => 'text-emerald-700 dark:text-emerald-400',
            'border' => 'border-emerald-200 dark:border-emerald-500/30',
            'icon'   => 'fa-circle-check'
        ],
        'terlambat'    => [
            'bg'     => 'bg-amber-50 dark:bg-amber-500/15',
            'text'   => 'text-amber-700 dark:text-amber-400',
            'border' => 'border-amber-200 dark:border-amber-500/30',
            'icon'   => 'fa-clock'
        ],
        'izin'         => [
            'bg'     => 'bg-sky-50 dark:bg-sky-500/15',
            'text'   => 'text-sky-700 dark:text-sky-400',
            'border' => 'border-sky-200 dark:border-sky-500/30',
            'icon'   => 'fa-paper-plane'
        ],
        'sakit'        => [
            'bg'     => 'bg-purple-50 dark:bg-purple-500/15',
            'text'   => 'text-purple-700 dark:text-purple-400',
            'border' => 'border-purple-200 dark:border-purple-500/30',
            'icon'   => 'fa-notes-medical'
        ],
        'alfa'         => [
            'bg'     => 'bg-rose-50 dark:bg-rose-500/15',
            'text'   => 'text-rose-700 dark:text-rose-400',
            'border' => 'border-rose-200 dark:border-rose-500/30',
            'icon'   => 'fa-triangle-exclamation'
        ],
        'pulang_cepat' => [
            'bg'     => 'bg-amber-50 dark:bg-amber-500/15',
            'text'   => 'text-amber-700 dark:text-amber-400',
            'border' => 'border-amber-200 dark:border-amber-500/30',
            'icon'   => 'fa-person-running'
        ],
        'pkl'          => [
            'bg'     => 'bg-indigo-50 dark:bg-indigo-500/15',
            'text'   => 'text-indigo-700 dark:text-indigo-400',
            'border' => 'border-indigo-200 dark:border-indigo-500/30',
            'icon'   => 'fa-briefcase'
        ],
        // Izin / Pengajuan
        'pending'      => [
            'bg'     => 'bg-slate-100 dark:bg-slate-800',
            'text'   => 'text-slate-700 dark:text-slate-300',
            'border' => 'border-slate-200 dark:border-slate-700',
            'icon'   => 'fa-clock'
        ],
        'approved'     => [
            'bg'     => 'bg-emerald-50 dark:bg-emerald-500/15',
            'text'   => 'text-emerald-700 dark:text-emerald-400',
            'border' => 'border-emerald-200 dark:border-emerald-500/30',
            'icon'   => 'fa-check-circle'
        ],
        'rejected'     => [
            'bg'     => 'bg-rose-50 dark:bg-rose-500/15',
            'text'   => 'text-rose-700 dark:text-rose-400',
            'border' => 'border-rose-200 dark:border-rose-500/30',
            'icon'   => 'fa-times-circle'
        ],
        // Jadwal / status umum
        'aktif'        => [
            'bg'     => 'bg-emerald-50 dark:bg-emerald-500/15',
            'text'   => 'text-emerald-700 dark:text-emerald-400',
            'border' => 'border-emerald-200 dark:border-emerald-500/30',
            'icon'   => 'fa-circle'
        ],
        'nonaktif'     => [
            'bg'     => 'bg-slate-100 dark:bg-slate-800',
            'text'   => 'text-slate-600 dark:text-slate-400',
            'border' => 'border-slate-200 dark:border-slate-700',
            'icon'   => 'fa-circle'
        ],
        'online'       => [
            'bg'     => 'bg-emerald-50 dark:bg-emerald-500/15',
            'text'   => 'text-emerald-700 dark:text-emerald-400',
            'border' => 'border-emerald-200 dark:border-emerald-500/30',
            'icon'   => 'fa-wifi'
        ],
        'offline'      => [
            'bg'     => 'bg-rose-50 dark:bg-rose-500/15',
            'text'   => 'text-rose-700 dark:text-rose-400',
            'border' => 'border-rose-200 dark:border-rose-500/30',
            'icon'   => 'fa-plug'
        ],
    ];

    /**
     * Render badge penuh: <span class="...">icon teks</span>
     * Bentuk pill rapi, font-medium, padding seimbang.
     */
    public static function render(string $status, ?string $label = null, ?string $icon = null): string
    {
        $key   = strtolower(str_replace([' ', '-'], '_', $status));
        $cfg   = self::$map[$key] ?? self::$map['pending'];
        $label = $label ?? strtoupper(str_replace('_', ' ', $status));
        $icon  = $icon  ?? $cfg['icon'];

        return sprintf(
            '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide border transition-colors %s %s %s">'
            . '<i class="fa-solid %s text-[10px]"></i><span>%s</span>'
            . '</span>',
            $cfg['bg'], $cfg['text'], $cfg['border'], $icon, htmlspecialchars($label)
        );
    }

    /**
     * Render badge tanpa label (hanya icon + warna), untuk inline indicator.
     */
    public static function dot(string $status): string
    {
        $key = strtolower($status);
        $cfg = self::$map[$key] ?? self::$map['pending'];
        $icon = $cfg['icon'];
        return sprintf('<i class="fa-solid %s %s"></i>', $icon, $cfg['text']);
    }

    /**
     * Render badge tanpa icon, hanya teks berwarna.
     */
    public static function text(string $status, string $label): string
    {
        $key = strtolower($status);
        $cfg = self::$map[$key] ?? self::$map['pending'];
        return sprintf(
            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold %s %s %s border transition-colors">%s</span>',
            $cfg['bg'], $cfg['text'], $cfg['border'], htmlspecialchars($label)
        );
    }
}