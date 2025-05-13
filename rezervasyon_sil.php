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
    header("Location: ../rezervasyonlar.php?durum=hata&mesaj=" . urlencode("Geçersiz rezervasyon ID."));
    exit;
}

$rezervasyon_id = intval($_GET['id']);

// Rezervasyon bilgilerini al
$rezervasyon_sorgu = $db->prepare("SELECT * FROM rezervasyonlar WHERE id = ?");
$rezervasyon_sorgu->execute([$rezervasyon_id]);

if ($rezervasyon_sorgu->rowCount() == 0) {
    header("Location: ../rezervasyonlar.php?durum=hata&mesaj=" . urlencode("Rezervasyon bulunamadı."));
    exit;
}

// Rezervasyon ile ilişkili ekstra hizmetleri sil
$ekstra_sil = $db->prepare("DELETE FROM rezervasyon_ekstralar WHERE rezervasyon_id = ?");
$ekstra_sil->execute([$rezervasyon_id]);

// Rezervasyonu sil
$sil_sorgu = $db->prepare("DELETE FROM rezervasyonlar WHERE id = ?");
$sil_sorgu->execute([$rezervasyon_id]);

if ($sil_sorgu->rowCount() > 0) {
    header("Location: ../rezervasyonlar.php?durum=silindi");
    exit;
} else {
    header("Location: ../rezervasyonlar.php?durum=hata&mesaj=" . urlencode("Rezervasyon silinirken bir hata oluştu."));
    exit;
}
?>