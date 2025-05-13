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

// POST verisi kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ozellik_id']) && is_array($_POST['ozellik_id'])) {
    $ozellik_ids = $_POST['ozellik_id'];
    
    // Özellik ID'lerini ve yeni sıra numaralarını kaydet
    try {
        $db->beginTransaction();
        
        $guncelle_sorgu = $db->prepare("UPDATE ozellikler SET sira = ? WHERE id = ?");
        
        foreach ($ozellik_ids as $sira => $id) {
            $guncelle_sorgu->execute([$sira + 1, $id]);
        }
        
        $db->commit();
        
        // Başarılı ise özellikler sayfasına yönlendir
        header("Location: ../ozellikler.php?durum=siralama");
        exit;
    } catch (PDOException $e) {
        $db->rollBack();
        header("Location: ../ozellikler.php?durum=hata&mesaj=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Geçersiz istek, ana sayfaya yönlendir
    header("Location: ../ozellikler.php");
    exit;
}
?>