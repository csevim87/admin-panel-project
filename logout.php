<?php
session_start();

// Admin oturumunu sonlandır
unset($_SESSION['admin_id']);
unset($_SESSION['admin_kullanici_adi']);
unset($_SESSION['admin_adi']);
unset($_SESSION['admin_soyadi']);
unset($_SESSION['admin_yetki']);

// Session'ı yok et
session_destroy();

// Giriş sayfasına yönlendir
header("Location: index.php");
exit;
?>