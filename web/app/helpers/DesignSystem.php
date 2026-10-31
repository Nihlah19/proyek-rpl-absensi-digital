<?php
/**
 * Design System Helper
 * 
 * Komponen:
 *  - Badge status warna
 *  - Tombol standar (primary, secondary, danger)
 *  - Input standar
 *  - Card standar
 *  - Alert standar
 *  - Table standar
 *  - Badge warna (untuk status siswa)
 *  - Badge warna guru
 * 
 * Contoh pakai:
 * <?= DesignSystem::buttonPrimary('Simpan', ['class' => 'px-4 py-2']) ?>
 * <?= DesignSystem::badge('hadir', 'Hadir') ?>
 */
class DesignSystem {
    
    /**
     * Warna badge
     * @var array
     */
    public static $badgeColors = [
        'primary'    => ['bg' => 'bg-primary/10',   'text' => 'text-primary',    'border' => 'border-primary/20'],
        'secondary'  => ['bg' => 'bg-secondary/10', 'text' => 'text-secondary',  'border' => 'border-secondary/20'],
        'success'    => ['bg' => 'bg-success/10',   'text' => 'text-success',   'border' => 'border-success/20'],
        'danger'     => ['bg' => 'bg-danger/10',    'text' => 'text-danger',    'border' => 'border-danger/20'],
        'warning'    => ['bg' => 'bg-warning/10',   'text' => 'text-warning',   'border' => 'border-warning/20'],
        'info'       => ['bg' => 'bg-info/10',      'text' => 'text-info',      'border' => 'border-info/20'],
        'muted'      => ['bg' => 'bg-slate-100',    'text' => 'text-slate-600', 'border' => 'border-slate-200'],
        'light'      => ['bg' => 'bg-white',        'text' => 'text-slate-700', 'border' => 'border-slate-200'],
        'dark'       => ['bg' => 'bg-slate-800',    'text' => 'text-white',     'border' => 'border-slate-700'],
    ];
    
    /**
     * Button styles
     * @var array
     */
    public static $buttonStyles = [
        'primary'   => 'bg-primary text-white hover:bg-primary-dark transition-colors',
        'secondary' => 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors',
        'success'   => 'bg-success text-white hover:bg-emerald-600 transition-colors',
        'danger'    => 'bg-danger text-white hover:bg-red-600 transition-colors',
        'warning'   => 'bg-warning text-white hover:bg-amber-600 transition-colors',
        'link'      => 'text-primary hover:text-primary-dark transition-colors',
        'ghost'     => 'text-slate-600 hover:bg-slate-100 transition-colors',
    ];
    
    /**
     * Input styles
     * @var array
     */
    public static $inputStyles = [
        'default' => 'block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition',
        'error'   => 'block w-full rounded-lg border border-danger/50 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-danger/30 focus:border-danger transition',
        'success' => 'block w-full rounded-lg border border-success/50 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-success/30 focus:border-success transition',
        'disabled'=> 'block w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-400 shadow-sm cursor-not-allowed',
        'textarea' => 'block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition resize-y',
        'select'   => 'block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition appearance-none',
    ];
    
    /**
     * Card styles
     * @var array
     */
    public static $cardStyles = [
        'default' => 'bg-white rounded-xl border border-slate-100 shadow-sm',
        'elevated'=> 'bg-white rounded-xl border border-slate-100 shadow-lg',
        'outlined'=> 'bg-transparent rounded-xl border-2 border-slate-200',
        'interactive' => 'bg-white rounded-xl border border-slate-100 shadow-sm hover:shadow-md hover:border-slate-200 transition-all duration-200',
    ];
    
    /**
     * Alert styles
     * @var array
     */
    public static $alertStyles = [
        'success' => 'bg-success/10 text-success border border-success/20 rounded-lg px-4 py-3 text-sm flex items-center gap-2',
        'danger'  => 'bg-danger/10 text-danger border border-danger/20 rounded-lg px-4 py-3 text-sm flex items-center gap-2',
        'warning' => 'bg-warning/10 text-warning border border-warning/20 rounded-lg px-4 py-3 text-sm flex items-center gap-2',
        'info'    => 'bg-primary/10 text-primary border border-primary/20 rounded-lg px-4 py-3 text-sm flex items-center gap-2',
        'muted'   => 'bg-slate-50 text-slate-600 border border-slate-200 rounded-lg px-4 py-3 text-sm flex items-center gap-2',
    ];
    
    /**
     * Status badge colors for siswa
     * @var array
     */
    public static $statusBadgeColors = [
        'hadir'      => ['bg' => 'bg-emerald-50',   'text' => 'text-emerald-700',  'border' => 'border-emerald-200'],
        'terlambat'  => ['bg' => 'bg-amber-50',     'text' => 'text-amber-700',   'border' => 'border-amber-200'],
        'izin'       => ['bg' => 'bg-sky-50',       'text' => 'text-sky-700',     'border' => 'border-sky-200'],
        'sakit'      => ['bg' => 'bg-rose-50',      'text' => 'text-rose-700',     'border' => 'border-rose-200'],
        'alfa'       => ['bg' => 'bg-danger/50',    'text' => 'text-danger',      'border' => 'border-danger/20'],
        'pkl'        => ['bg' => 'bg-indigo-100',   'text' => 'text-indigo-700',  'border' => 'border-indigo-200'],
        'pending'    => ['bg' => 'bg-slate-50',     'text' => 'text-slate-600',   'border' => 'border-slate-200'],
        'approved'   => ['bg' => 'bg-emerald-50',   'text' => 'text-emerald-700',  'border' => 'border-emerald-200'],
        'rejected'   => ['bg' => 'bg-danger/50',    'text' => 'text-danger',      'border' => 'border-danger/20'],
        'aktif'      => ['bg' => 'bg-emerald-50',   'text' => 'text-emerald-700',  'border' => 'border-emerald-200'],
        'nonaktif'   => ['bg' => 'bg-slate-50',     'text' => 'text-slate-500',   'border' => 'border-slate-200'],
        'online'     => ['bg' => 'bg-emerald-50',   'text' => 'text-emerald-700',  'border' => 'border-emerald-200'],
        'offline'    => ['bg' => 'bg-danger/50',    'text' => 'text-danger',      'border' => 'border-danger/20'],
    ];
    
    // ───────────────────────────────────────────────
    // Helper methods
    // ───────────────────────────────────────────────
    
    /**
     * Badge sederhana
     */
    public static function badge($type, $label, $extraClass = '') {
        $colors = self::$badgeColors[$type] ?? self::$badgeColors['muted'];
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colors['bg'] . ' ' . $colors['text'] . ' ' . $colors['border'] . ($extraClass ? ' ' . $extraClass : '') . '">' . $label . '</span>';
    }
    
    /**
     * Button primary
     */
    public static function buttonPrimary($label, $attrs = []) {
        $class = self::$buttonStyles['primary'] . ($attrs['class'] ?? '');
        $extra = self::buildAttrs($attrs, ['class']);
        return '<button class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg shadow-sm ' . $class . '"' . $extra . '>' . $label . '</button>';
    }
    
    /**
     * Tombol dangerous
     */
    public static function buttonDanger($label, $attrs = []) {
        $class = self::$buttonStyles['danger'] . ($attrs['class'] ?? '');
        $extra = self::buildAttrs($attrs, ['class']);
        return '<button class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg shadow-sm ' . $class . '"' . $extra . '>' . $label . '</button>';
    }
    
    /**
     * Tombol link
     */
    public static function buttonLink($label, $attrs = []) {
        $class = self::$buttonStyles['link'] . ($attrs['class'] ?? '');
        $extra = self::buildAttrs($attrs, ['class']);
        return '<button class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg ' . $class . '"' . $extra . '>' . $label . '</button>';
    }
    
    /**
     * Tombol secondary
     */
    public static function buttonSecondary($label, $attrs = []) {
        $class = self::$buttonStyles['secondary'] . ($attrs['class'] ?? '');
        $extra = self::buildAttrs($attrs, ['class']);
        return '<button class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg border ' . $class . '"' . $extra . '>' . $label . '</button>';
    }
    
    /**
     * Tombol logout
     */
    public static function buttonLogout($label = 'Logout', $attrs = []) {
        $class = 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors';
        $class .= ($attrs['class'] ?? '');
        $extra = self::buildAttrs($attrs, ['class']);
        return '<a href="' . BASEURL . '/auth/logout" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg border ' . $class . '"' . $extra . '>' . $label . '</a>';
    }
    
    /**
     * Input teks
     */
    public static function input($name, $label = null, $value = '', $attrs = []) {
        $class = self::$inputStyles['default'] . ($attrs['class'] ?? '');
        $disabled = $attrs['disabled'] ?? false;
        $extra = self::buildAttrs($attrs, ['class', 'disabled']);
        $html = '';
        if ($label) {
            $html .= '<label for="' . $name . '" class="block text-sm font-medium text-slate-700 mb-1.5">' . $label . '</label>';
        }
        $html .= '<input type="text" id="' . $name . '" name="' . $name . '" value="' . htmlspecialchars($value) . '" class="' . $class . '"' . ($disabled ? ' disabled' : '') . $extra . '>';
        return $html;
    }
    
    /**
     * Select
     */
    public static function select($name, $options = [], $selected = '', $attrs = []) {
        $class = self::$inputStyles['select'] . ($attrs['class'] ?? '');
        $extra = self::buildAttrs($attrs, ['class']);
        $html = '';
        if (isset($attrs['label'])) {
            $html .= '<label for="' . $name . '" class="block text-sm font-medium text-slate-700 mb-1.5">' . $attrs['label'] . '</label>';
        }
        $html .= '<select id="' . $name . '" name="' . $name . '" class="' . $class . '"' . $extra . '>';
        foreach ($options as $val => $text) {
            $sel = ($val == $selected) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($val) . '"' . $sel . '>' . htmlspecialchars($text) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
    
    /**
     * Textarea
     */
    public static function textarea($name, $label = null, $value = '', $attrs = []) {
        $class = self::$inputStyles['textarea'] . ($attrs['class'] ?? '');
        $extra = self::buildAttrs($attrs, ['class']);
        $html = '';
        if ($label) {
            $html .= '<label for="' . $name . '" class="block text-sm font-medium text-slate-700 mb-1.5">' . $label . '</label>';
        }
        $html .= '<textarea id="' . $name . '" name="' . $name . '" class="' . $class . '"' . $extra . '>' . htmlspecialchars($value) . '</textarea>';
        return $html;
    }
    
    /**
     * Tombol submit
     */
    public static function buttonSubmit($label, $attrs = []) {
        $class = 'bg-primary text-white hover:bg-primary-dark transition-colors shadow-sm';
        $class .= ($attrs['class'] ?? '');
        $extra = self::buildAttrs($attrs, ['class']);
        return '<button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium rounded-lg ' . $class . '"' . $extra . '>' . $label . '</button>';
    }
    
    /**
     * Alert
     */
    public static function alert($type, $message, $attrs = []) {
        $class = self::$alertStyles[$type] ?? self::$alertStyles['muted'] . ($attrs['class'] ?? '');
        $extra = self::buildAttrs($attrs, ['class']);
        return '<div class="' . $class . '"' . $extra . '>' . $message . '</div>';
    }
    
    /**
     * Card wrapper
     */
    public static function card($content, $type = 'default', $attrs = []) {
        $class = self::$cardStyles[$type] ?? self::$cardStyles['default'] . ($attrs['class'] ?? '');
        $extra = self::buildAttrs($attrs, ['class']);
        return '<div class="' . $class . '"' . $extra . '>' . $content . '</div>';
    }
    
    /**
     * Table with Tailwind styling
     */
    public static function table($headers = [], $rows = [], $attrs = []) {
        $class = 'w-full divide-y divide-slate-200' . ($attrs['class'] ?? '');
        $extra = self::buildAttrs($attrs, ['class']);
        $html = '<table class="' . $class . '"' . $extra . '>';
        if (!empty($headers)) {
            $html .= '<thead class="bg-slate-50"><tr>';
            foreach ($headers as $header) {
                $html .= '<th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">' . $header . '</th>';
            }
            $html .= '</tr></thead>';
        }
        $html .= '<tbody class="bg-white">';
        foreach ($rows as $row) {
            $html .= '<tr class="hover:bg-slate-50 transition-colors">';
            foreach ($row as $cell) {
                $html .= '<td class="px-4 py-3 text-sm text-slate-700">' . $cell . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }
    
    /**
     * Statistik card untuk dashboard
     */
    public static function statCard($label, $value, $icon, $color = 'primary', $subtext = '') {
        $iconColors = [
            'primary' => 'bg-primary/10 text-primary',
            'success' => 'bg-success/10 text-success',
            'danger'  => 'bg-danger/10 text-danger',
            'warning' => 'bg-warning/10 text-warning',
            'info'    => 'bg-info/10 text-info',
        ];
        $iconColor = $iconColors[$color] ?? 'bg-primary/10 text-primary';
        $html = '<div class="bg-slate-50 rounded-lg p-4">';
        $html .= '<p class="text-xs text-slate-400 uppercase tracking-wide">' . $label . '</p>';
        $html .= '<p class="text-2xl font-bold text-slate-800 mt-1">' . $value . '</p>';
        $html .= '<div class="mt-2 flex items-center gap-1 text-sm text-slate-600"><i class="fa-solid ' . $icon . '"></i>' . ($subtext ? '(' . $subtext . ')' : '') . '</div>';
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Empty state
     */
    public static function emptyState($icon, $message, $action = null) {
        $html = '<div class="flex flex-col items-center justify-center py-12 text-slate-400">';
        $html .= '<i class="fa-solid ' . $icon . ' text-4xl mb-4"></i>';
        $html .= '<p class="text-sm">' . $message . '</p>';
        if ($action) {
            $html .= '<p class="text-xs text-slate-400 mt-2">' . $action . '</p>';
        }
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Badge status untuk tabel
     */
    public static function statusBadge($status) {
        $colors = self::$statusBadgeColors[$status] ?? self::$statusBadgeColors['muted'];
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colors['bg'] . ' ' . $colors['text'] . ' ' . $colors['border'] . '">' . strtoupper($status) . '</span>';
    }
    
    /**
     * Button card (untuk navigasi grid)
     */
    public static function cardButton($href, $label, $icon, $color = 'primary') {
        $hoverColors = [
            'primary'  => 'hover:border-primary/30 hover:bg-primary/5',
            'success'  => 'hover:border-success/30 hover:bg-success/5',
            'danger'   => 'hover:border-danger/30 hover:bg-danger/5',
            'warning'  => 'hover:border-warning/30 hover:bg-warning/5',
            'info'     => 'hover:border-info/30 hover:bg-info/5',
            'secondary'=> 'hover:border-secondary/30 hover:bg-secondary/5',
            'accent'   => 'hover:border-accent/30 hover:bg-accent/5',
            'muted'    => 'hover:border-slate-300 hover:bg-slate-50',
        ];
        $hover = $hoverColors[$color] ?? $hoverColors['muted'];
        $html = '<a href="' . BASEURL . '/' . $href . '" class="group p-4 rounded-lg border border-slate-100 hover:border-' . $color . '/30 hover:bg-' . $color . '/5 transition-all">';
        $html .= '<div class="flex items-center justify-center h-12 w-12 rounded-lg bg-' . $color . '/10 text-' . $color . ' mb-3 group-hover:bg-' . $color . '/20 transition-colors">';
        $html .= '<i class="fa-solid ' . $icon . '"></i>';
        $html .= '</div>';
        $html .= '<p class="font-medium text-slate-700 group-hover:text-' . $color . ' transition-colors">' . $label . '</p>';
        $html .= '</a>';
        return $html;
    }
    
    /**
     * Badge dengan icon
     */
    public static function badgeWithIcon($type, $label, $icon = '') {
        $colors = self::$badgeColors[$type] ?? self::$badgeColors['muted'];
        if ($icon) {
            return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colors['bg'] . ' ' . $colors['text'] . ' ' . $colors['border'] . '"><i class="fa-solid ' . $icon . '"></i>' . $label . '</span>';
        }
        return self::badge($type, $label);
    }
    
    /**
     * Helper: build attributes
     */
    private static function buildAttrs($attrs = [], $exclude = []) {
        $parts = [];
        foreach ($attrs as $key => $value) {
            if (in_array($key, $exclude)) continue;
            if (is_bool($value)) {
                if ($value) $parts[] = $key;
            } else {
                $parts[] = $key . '="' . htmlspecialchars($value) . '"';
            }
        }
        return count($parts) ? ' ' . implode(' ', $parts) : '';
    }
}

/* Registrasi helper */
if (!function_exists('ds')) {
    function ds($method, ...$args) {
        return DesignSystem::$method(...$args);
    }
}