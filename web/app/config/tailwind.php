<?php
/**
 * Load Tailwind Configuration
 * 
 * File ini harus di-include di header.php atau init.php
 * 
 * Cara pakai di view:
 * <?php $this->view('config/tailwind'); ?>
 * 
 * ATAU di include di init.php:
 * require_once 'config/tailwind.php';
 *
 * Kemudian di view, Tailwind config akan otomatis ter-load
 * jika belum diinisialisasi.
 */

// Load konfigurasi warna
$GLOBALS['TW_COLORS'] = TW_COLORS;

// Helper function untuk mendapatkan warna Tailwind
if (!function_exists('tw_color')) {
    function tw_color($key) {
        global $GLOBALS;
        $colors = $GLOBALS['TW_COLORS'] ?? [];
        return $colors[$key] ?? null;
    }
}

// Simpan flag inisialisasi
define('TAILWIND_CONFIG_LOADED', true);