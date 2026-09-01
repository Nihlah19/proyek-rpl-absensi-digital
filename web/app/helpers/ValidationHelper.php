<?php
class ValidationHelper {
    public static function sanitize($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    public static function required($fields, $data) {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
}
