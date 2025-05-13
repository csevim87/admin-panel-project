<?php
session_start();
require_once '../include/config.php';
require_once '../include/db.php';
require_once '../include/functions.php';

// Kullanıcı zaten giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$hata = '';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici_adi = isset($_POST['kullanici_adi']) ? guvenliVeri($_POST['kullanici_adi']) : '';
    $sifre = isset($_POST['sifre']) ? $_POST['sifre'] : '';
    
    if (empty($kullanici_adi) || empty($sifre)) {
        $hata = 'Kullanıcı adı ve şifre gereklidir.';
    } else {
        // Kullanıcıyı veritabanında ara
        $admin_sorgu = $db->prepare("SELECT * FROM admin_kullanicilar WHERE kullanici_adi = ? AND durum = 1");
        $admin_sorgu->execute([$kullanici_adi]);
        
        if ($admin_sorgu->rowCount() > 0) {
            $admin = $admin_sorgu->fetch();
            
            // Şifreyi doğrula
            if (password_verify($sifre, $admin['sifre'])) {
                // Giriş başarılı, oturum bilgilerini ayarla
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_kullanici_adi'] = $admin['kullanici_adi'];
                $_SESSION['admin_adi'] = $admin['adi'];
                $_SESSION['admin_soyadi'] = $admin['soyadi'];
                $_SESSION['admin_yetki'] = $admin['yetki'];
                
                // Son giriş zamanını güncelle
                $guncelle = $db->prepare("UPDATE admin_kullanicilar SET son_giris = NOW() WHERE id = ?");
                $guncelle->execute([$admin['id']]);
                
                // Dashboard'a yönlendir
                header("Location: dashboard.php");
                exit;
            } else {
                $hata = 'Hatalı kullanıcı adı veya şifre.';
            }
        } else {
            $hata = 'Hatalı kullanıcı adı veya şifre.';
        }
    }
}

// Admin kontrol tablosu mevcut değilse, ilk admin kullanıcısını oluştur
$tablo_kontrol = $db->query("SHOW TABLES LIKE 'admin_kullanicilar'");
if ($tablo_kontrol->rowCount() == 0) {
    // Tabloyu oluştur
    $db->query("
        CREATE TABLE `admin_kullanicilar` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `kullanici_adi` varchar(50) NOT NULL,
          `sifre` varchar(255) NOT NULL,
          `adi` varchar(100) NOT NULL,
          `soyadi` varchar(100) NOT NULL,
          `email` varchar(255) NOT NULL,
          `yetki` enum('admin','yonetici','personel') NOT NULL DEFAULT 'personel',
          `durum` tinyint(1) NOT NULL DEFAULT '1',
          `olusturma_tarihi` datetime NOT NULL,
          `son_giris` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `kullanici_adi` (`kullanici_adi`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
    
    // İlk admin kullanıcısını oluştur
    $sifre_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $db->query("
        INSERT INTO `admin_kullanicilar` 
        (`kullanici_adi`, `sifre`, `adi`, `soyadi`, `email`, `yetki`, `durum`, `olusturma_tarihi`) 
        VALUES 
        ('admin', '{$sifre_hash}', 'Admin', 'Kullanıcı', 'admin@example.com', 'admin', 1, NOW())
    ");
    
    $ilk_kurulum = true;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fc;
        }
        
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: #4e73df;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 20px;
        }
        
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <?php if (isset($ilk_kurulum)): ?>
                <div class="alert alert-info">
                    <p><strong>İlk kurulum tamamlandı!</strong></p>
                    <p>Admin girişi için aşağıdaki bilgileri kullanabilirsiniz:</p>
                    <p><strong>Kullanıcı Adı:</strong> admin</p>
                    <p><strong>Şifre:</strong> admin123</p>
                    <p class="mb-0">Giriş yaptıktan sonra lütfen şifrenizi değiştirin.</p>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><?php echo SITE_NAME; ?> - Admin Girişi</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($hata)): ?>
                        <div class="alert alert-danger">
                            <?php echo $hata; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="kullanici_adi">Kullanıcı Adı</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" class="form-control" id="kullanici_adi" name="kullanici_adi" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="sifre">Şifre</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password" class="form-control" id="sifre" name="sifre" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block btn-lg">Giriş Yap</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light text-center py-3">
                    <a href="<?php echo SITE_URL; ?>" class="text-muted"><i class="fas fa-arrow-left mr-2"></i>Siteye Dön</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery ve Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>