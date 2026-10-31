<?php
require_once __DIR__ . '/../app/init.php';
$db = new Database();
try {
    $db->query("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL AFTER name");
    $db->execute();
    echo "Column avatar added.";
} catch (Exception $e) {
    echo "Error or already exists: " . $e->getMessage();
}
