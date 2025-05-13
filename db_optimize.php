<?php
session_start();
require_once '../../include/config.php';
require_once '../../include/db.php';
require_once '../../include/functions.php';

// Oturum kontrolü
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_yetki'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

try {
    // Tüm tabloları al
    $tables_sorgu = $db->query('SHOW TABLES');
    $tables = [];
    while ($row = $tables_sorgu->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    // Her tablo için optimize et
    $optimized_tables = [];
    foreach ($tables as $table) {
        $optimize_sorgu = $db->query("OPTIMIZE TABLE {$table}");
        $optimized_tables[] = $table;
    }
    
    // Başarı mesajı ile yönlendir
    header("Location: ../ayarlar.php?durum=optimize_success&tables=" . count($optimized_tables));
    exit;
} catch (PDOException $e) {
    // Hata mesajı ile yönlendir
    header("Location: ../ayarlar.php?durum=hata&mesaj=" . urlencode("Veritabanı optimizasyonu sırasında bir hata oluştu: " . $e->getMessage()));
    exit;
}
?>