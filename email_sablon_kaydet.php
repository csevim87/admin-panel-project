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

// POST verisi kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../ayarlar.php");
    exit;
}

// Form verilerini al
$sablon_tipi = isset($_POST['sablon_tipi']) ? guvenliVeri($_POST['sablon_tipi']) : '';
$konu = isset($_POST['konu']) ? guvenliVeri($_POST['konu']) : '';
$icerik = isset($_POST['icerik']) ? $_POST['icerik'] : '';

// Doğrulama
if (empty($sablon_tipi) || empty($konu) || empty($icerik)) {
    header("Location: ../ayarlar.php?durum=hata&mesaj=" . urlencode("Tüm alanları doldurun."));
    exit;
}

// Geçerli şablon tipleri
$gecerli_tipler = ['rezervasyon_onay', 'rezervasyon_iptal', 'rezervasyon_degisiklik', 'giris_hatirlatma'];

if (!in_array($sablon_tipi, $gecerli_tipler)) {
    header("Location: ../ayarlar.php?durum=hata&mesaj=" . urlencode("Geçersiz şablon tipi."));
    exit;
}

// E-posta şablon tablosu var mı kontrol et, yoksa oluştur
$tablo_kontrol = $db->query("SHOW TABLES LIKE 'email_sablonlar'");
if ($tablo_kontrol->rowCount() == 0) {
    $db->query("
        CREATE TABLE `email_sablonlar` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `tip` varchar(50) NOT NULL,
          `konu` varchar(255) NOT NULL,
          `icerik` text NOT NULL,
          `guncelleme_tarihi` datetime NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `tip` (`tip`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
}

// Şablon veritabanında var mı kontrol et
$sablon_sorgu = $db->prepare("SELECT * FROM email_sablonlar WHERE tip = ?");
$sablon_sorgu->execute([$sablon_tipi]);

if ($sablon_sorgu->rowCount() > 0) {
    // Şablonu güncelle
    $guncelle = $db->prepare("
        UPDATE email_sablonlar 
        SET konu = ?, icerik = ?, guncelleme_tarihi = NOW() 
        WHERE tip = ?
    ");
    
    $guncelle->execute([$konu, $icerik, $sablon_tipi]);
} else {
    // Yeni şablon ekle
    $ekle = $db->prepare("
        INSERT INTO email_sablonlar (tip, konu, icerik, guncelleme_tarihi)
        VALUES (?, ?, ?, NOW())
    ");
    
    $ekle->execute([$sablon_tipi, $konu, $icerik]);
}

// Başarı mesajı ile yönlendir
header("Location: ../ayarlar.php?durum=sablon_kaydedildi");
exit;
?>