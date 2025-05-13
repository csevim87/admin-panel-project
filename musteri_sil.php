<?php
session_start();
require_once '../../include/config.php';
require_once '../../include/db.php';
require_once '../../include/functions.php';

// Oturum kontrolü
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit;
}

// ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../musteriler.php?durum=hata&mesaj=" . urlencode("Geçersiz müşteri ID."));
    exit;
}

$musteri_id = intval($_GET['id']);

// Müşteri bilgilerini al
$musteri_sorgu = $db->prepare("SELECT * FROM musteriler WHERE id = ?");
$musteri_sorgu->execute([$musteri_id]);

if ($musteri_sorgu->rowCount() == 0) {
    header("Location: ../musteriler.php?durum=hata&mesaj=" . urlencode("Müşteri bulunamadı."));
    exit;
}

try {
    $db->beginTransaction();
    
    // Müşteriye ait rezervasyonları al
    $rezervasyon_sorgu = $db->prepare("SELECT id FROM rezervasyonlar WHERE musteri_id = ?");
    $rezervasyon_sorgu->execute([$musteri_id]);
    $rezervasyonlar = $rezervasyon_sorgu->fetchAll(PDO::FETCH_COLUMN);
    
    // Rezervasyonlara ait ekstra hizmetleri sil
    if (!empty($rezervasyonlar)) {
        $ekstra_sil = $db->prepare("DELETE FROM rezervasyon_ekstralar WHERE rezervasyon_id IN (" . implode(',', array_fill(0, count($rezervasyonlar), '?')) . ")");
        $ekstra_sil->execute($rezervasyonlar);
        
        // Rezervasyonları sil
        $rezervasyon_sil = $db->prepare("DELETE FROM rezervasyonlar WHERE musteri_id = ?");
        $rezervasyon_sil->execute([$musteri_id]);
    }
    
    // Müşteriyi sil
    $musteri_sil = $db->prepare("DELETE FROM musteriler WHERE id = ?");
    $musteri_sil->execute([$musteri_id]);
    
    $db->commit();
    
    header("Location: ../musteriler.php?durum=silindi");
    exit;
} catch (PDOException $e) {
    $db->rollBack();
    header("Location: ../musteriler.php?durum=hata&mesaj=" . urlencode("Müşteri silinirken bir hata oluştu: " . $e->getMessage()));
    exit;
}
?>