<?php
class ValidationHelper {
    public static function sanitize($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    /**
     * Cek apakah semua nilai dalam array tidak kosong.
     * Mendukung dua mode:
     * - required([$val1, $val2, ...]) — array nilai langsung
     * - required(['field1','field2'], $data) — array nama field + data array
     */
    public static function required($fields, $data = null) {
        if ($data !== null && is_array($data)) {
            // Mode: array nama field + array data
            foreach ($fields as $field) {
                if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                    return false;
                }
            }
        } else {
            // Mode: array nilai langsung
            foreach ($fields as $value) {
                if ($value === '' || $value === null || (is_string($value) && trim($value) === '')) {
                    return false;
                }
            }
        }
        return true;
    }
}
