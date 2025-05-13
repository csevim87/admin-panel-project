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
    header("Location: ../odalar.php?durum=hata&mesaj=" . urlencode("Geçersiz oda ID."));
    exit;
}

$oda_id = intval($_GET['id']);

// Oda bilgilerini al
$oda_sorgu = $db->prepare("SELECT * FROM odalar WHERE id = ?");
$oda_sorgu->execute([$oda_id]);

if ($oda_sorgu->rowCount() == 0) {
    header("Location: ../odalar.php?durum=hata&mesaj=" . urlencode("Oda bulunamadı."));
    exit;
}

$oda = $oda_sorgu->fetch();

// Odaya ait rezervasyonlar var mı kontrol et
$rezervasyon_sorgu = $db->prepare("SELECT COUNT(*) as sayi FROM rezervasyonlar WHERE oda_id = ?");
$rezervasyon_sorgu->execute([$oda_id]);
$rezervasyon_sayisi = $rezervasyon_sorgu->fetch()['sayi'];

if ($rezervasyon_sayisi > 0) {
    header("Location: ../odalar.php?durum=hata&mesaj=" . urlencode("Bu odaya ait {$rezervasyon_sayisi} adet rezervasyon bulunmaktadır. Önce rezervasyonları silmelisiniz."));
    exit;
}

try {
    $db->beginTransaction();
    
    // Odaya ait özellikleri sil
    $ozellik_sil = $db->prepare("DELETE FROM oda_ozellikler WHERE oda_id = ?");
    $ozellik_sil->execute([$oda_id]);
    
    // Odayı sil
    $sil_sorgu = $db->prepare("DELETE FROM odalar WHERE id = ?");
    $sil_sorgu->execute([$oda_id]);
    
    // Resmi sil
    if (!empty($oda['resim']) && file_exists('../../images/odalar/' . $oda['resim'])) {
        unlink('../../images/odalar/' . $oda['resim']);
    }
    
    $db->commit();
    
    header("Location: ../odalar.php?durum=silindi");
    exit;
} catch (PDOException $e) {
    $db->rollBack();
    header("Location: ../odalar.php?durum=hata&mesaj=" . urlencode("Oda silinirken bir hata oluştu: " . $e->getMessage()));
    exit;
}
?>