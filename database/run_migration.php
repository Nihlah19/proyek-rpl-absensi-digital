<?php
require_once __DIR__ . '/../web/app/init.php';

$db = new Database();
$sql = file_get_contents(__DIR__ . '/migrations/pulang_cepat_migration.sql');
$db->query($sql);
try {
    $db->execute();
    echo "Migration successful.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
