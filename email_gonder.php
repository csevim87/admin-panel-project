<?php
session_start();
require_once '../../include/config.php';
require_once '../../include/db.php';
require_once '../../include/functions.php';
require_once '../../include/email.php';

// Oturum kontrolü
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit;
}

// POST verisi kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

// E-posta bilgilerini al
$alici = isset($_POST['alici']) ? guvenliVeri($_POST['alici']) : '';
$konu = isset($_POST['konu']) ? guvenliVeri($_POST['konu']) : '';
$mesaj = isset($_POST['mesaj']) ? $_POST['mesaj'] : '';
$rezervasyon_id = isset($_POST['rezervasyon_id']) ? intval($_POST['rezervasyon_id']) : 0;
$musteri_id = isset($_POST['musteri_id']) ? intval($_POST['musteri_id']) : 0;

// Doğrulama
if (empty($alici) || empty($konu) || empty($mesaj)) {
    if ($rezervasyon_id > 0) {
        header("Location: ../rezervasyon_detay.php?id={$rezervasyon_id}&durum=hata&mesaj=" . urlencode("E-posta gönderilemedi. Tüm alanları doldurun."));
    } else if ($musteri_id > 0) {
        header("Location: ../musteri_detay.php?id={$musteri_id}&durum=hata&mesaj=" . urlencode("E-posta gönderilemedi. Tüm alanları doldurun."));
    } else {
        header("Location: ../index.php?durum=hata&mesaj=" . urlencode("E-posta gönderilemedi. Tüm alanları doldurun."));
    }
    exit;
}

// E-posta gönder
$gonderim_basarili = emailGonder($alici, $konu, $mesaj);

// Gönderim sonucu
if ($gonderim_basarili) {
    // E-posta gönderim logunu kaydet
    $log_sorgu = $db->prepare("
        INSERT INTO email_log (alici, konu, mesaj, gonderim_tarihi, gonderim_durumu, admin_id, rezervasyon_id, musteri_id)
        VALUES (?, ?, ?, NOW(), 1, ?, ?, ?)
    ");
    
    $log_sorgu->execute([$alici, $konu, $mesaj, $_SESSION['admin_id'], $rezervasyon_id, $musteri_id]);
    
    // Başarılı mesajı ile yönlendir
    if ($rezervasyon_id > 0) {
        header("Location: ../rezervasyon_detay.php?id={$rezervasyon_id}&durum=email_gonderildi");
    } else if ($musteri_id > 0) {
        header("Location: ../musteri_detay.php?id={$musteri_id}&durum=email_gonderildi");
    } else {
        header("Location: ../index.php?durum=email_gonderildi");
    }
    exit;
} else {
    // Hata mesajı ile yönlendir
    if ($rezervasyon_id > 0) {
        header("Location: ../rezervasyon_detay.php?id={$rezervasyon_id}&durum=hata&mesaj=" . urlencode("E-posta gönderilemedi."));
    } else if ($musteri_id > 0) {
        header("Location: ../musteri_detay.php?id={$musteri_id}&durum=hata&mesaj=" . urlencode("E-posta gönderilemedi."));
    } else {
        header("Location: ../index.php?durum=hata&mesaj=" . urlencode("E-posta gönderilemedi."));
    }
    exit;
}
?>