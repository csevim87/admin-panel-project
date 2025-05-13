<?php
session_start();
require_once '../include/config.php';
require_once '../include/db.php';
require_once '../include/functions.php';

// Sayfa başlığı
$sayfa_baslik = 'Yeni Oda Ekle';

// Özellikleri getir
$ozellik_sorgu = $db->query("SELECT * FROM ozellikler WHERE durum = 1 ORDER BY sira ASC");
$ozellikler = $ozellik_sorgu->fetchAll();

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al
    $adi = isset($_POST['adi']) ? guvenliVeri($_POST['adi']) : '';
    $kisa_aciklama = isset($_POST['kisa_aciklama']) ? guvenliVeri($_POST['kisa_aciklama']) : '';
    $aciklama = isset($_POST['aciklama']) ? $_POST['aciklama'] : ''; // HTML olabilir
    $kapasite = isset($_POST['kapasite']) ? intval($_POST['kapasite']) : 2;
    $max_kapasite = isset($_POST['max_kapasite']) ? intval($_POST['max_kapasite']) : 2;
    $metrekare = isset($_POST['metrekare']) ? intval($_POST['metrekare']) : 0;
    $taban_fiyat = isset($_POST['taban_fiyat']) ? floatval($_POST['taban_fiyat']) : 0;
    $ek_yetiskin_fiyat = isset($_POST['ek_yetiskin_fiyat']) ? floatval($_POST['ek_yetiskin_fiyat']) : 0;
    $buyuk_cocuk_fiyat = isset($_POST['buyuk_cocuk_fiyat']) ? floatval($_POST['buyuk_cocuk_fiyat']) : 0;
    $kucuk_cocuk_fiyat = isset($_POST['kucuk_cocuk_fiyat']) ? floatval($_POST['kucuk_cocuk_fiyat']) : 0;
    $stok = isset($_POST['stok']) ? intval($_POST['stok']) : 1;
    $durum = isset($_POST['durum']) ? intval($_POST['durum']) : 1;
    $sira = isset($_POST['sira']) ? intval($_POST['sira']) : 0;
    
    // Seçilen özellikler
    $secilen_ozellikler = isset($_POST['ozellikler']) ? $_POST['ozellikler'] : [];
    
    // Doğrulama
    $hatalar = [];
    
    if (empty($adi)) {
        $hatalar[] = "Oda adı boş bırakılamaz.";
    }
    
    if ($taban_fiyat <= 0) {
        $hatalar[] = "Taban fiyat 0'dan büyük olmalıdır.";
    }
    
    if ($kapasite <= 0) {
        $hatalar[] = "Kapasite 0'dan büyük olmalıdır.";
    }
    
    if ($max_kapasite < $kapasite) {
        $hatalar[] = "Maksimum kapasite, standart kapasiteden küçük olamaz.";
    }
    
    if ($stok <= 0) {
        $hatalar[] = "Stok 0'dan büyük olmalıdır.";
    }
    
    // Resim yükleme işlemi
    $resim = '';
    if (isset($_FILES['resim']) && $_FILES['resim']['error'] == 0) {
        $izin_verilen_uzantilar = ['jpg', 'jpeg', 'png', 'webp'];
        $dosya_uzantisi = strtolower(pathinfo($_FILES['resim']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($dosya_uzantisi, $izin_verilen_uzantilar)) {
            $hatalar[] = "Sadece JPG, JPEG, PNG ve WEBP formatları desteklenmektedir.";
        } else {
            $max_boyut = 5 * 1024 * 1024; // 5MB
            if ($_FILES['resim']['size'] > $max_boyut) {
                $hatalar[] = "Resim dosyası 5MB'dan büyük olamaz.";
            } else {
                // Yeni dosya adı oluştur
                $yeni_dosya_adi = seo($adi) . '-' . uniqid() . '.' . $dosya_uzantisi;
                $hedef_dizin = '../images/odalar/';
                $hedef_yol = $hedef_dizin . $yeni_dosya_adi;
                
                // Dizin yoksa oluştur
                if (!file_exists($hedef_dizin)) {
                    mkdir($hedef_dizin, 0777, true);
                }
                
                // Resmi yükle
                if (move_uploaded_file($_FILES['resim']['tmp_name'], $hedef_yol)) {
                    $resim = $yeni_dosya_adi;
                } else {
                    $hatalar[] = "Resim yüklenirken bir hata oluştu.";
                }
            }
        }
    }
    
    // Hata yoksa oda ekle
    if (empty($hatalar)) {
        try {
            $db->beginTransaction();
            
            // Oda ekle
            $oda_ekle = $db->prepare("
                INSERT INTO odalar (
                    adi, kisa_aciklama, aciklama, kapasite, max_kapasite, metrekare,
                    taban_fiyat, ek_yetiskin_fiyat, buyuk_cocuk_fiyat, kucuk_cocuk_fiyat,
                    stok, resim, durum, sira
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");
            
            $oda_ekle->execute([
                $adi, $kisa_aciklama, $aciklama, $kapasite, $max_kapasite, $metrekare,
                $taban_fiyat, $ek_yetiskin_fiyat, $buyuk_cocuk_fiyat, $kucuk_cocuk_fiyat,
                $stok, $resim, $durum, $sira
            ]);
            
            $oda_id = $db->lastInsertId();
            
            // Oda özellikleri ekle
            if (!empty($secilen_ozellikler)) {
                $ozellik_ekle = $db->prepare("INSERT INTO oda_ozellikler (oda_id, ozellik_id) VALUES (?, ?)");
                
                foreach ($secilen_ozellikler as $ozellik_id) {
                    $ozellik_ekle->execute([$oda_id, $ozellik_id]);
                }
            }
            
            $db->commit();
            
            // İşlem başarılı, odalar sayfasına yönlendir
            header("Location: oda_detay.php?id=$oda_id&durum=eklendi");
            exit;
        } catch (PDOException $e) {
            $db->rollBack();
            $hata_mesaj = "Veritabanı hatası: " . $e->getMessage();
        }
    }
}

// En son sıra numarasını al
$son_sira_sorgu = $db->query("SELECT MAX(sira) as son_sira FROM odalar");
$son_sira = $son_sira_sorgu->fetch()['son_sira'];
$sira = $son_sira + 1;

// Header kısmını dahil et
include 'include/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Yeni Oda Ekle</h1>
    <a href="odalar.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Odalara Dön
    </a>
</div>

<?php if (isset($hatalar) && !empty($hatalar)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($hatalar as $hata): ?>
            <li><?php echo $hata; ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if (isset($hata_mesaj)): ?>
<div class="alert alert-danger">
    <?php echo $hata_mesaj; ?>
</div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Oda Bilgileri</h6>
    </div>
    <div class="card-body">
        <form method="post" action="" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="adi">Oda Adı</label>
                        <input type="text" class="form-control" id="adi" name="adi" value="<?php echo isset($adi) ? $adi : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kisa_aciklama">Kısa Açıklama</label>
                        <textarea class="form-control" id="kisa_aciklama" name="kisa_aciklama" rows="3"><?php echo isset($kisa_aciklama) ? $kisa_aciklama : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="resim">Oda Resmi</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="resim" name="resim" accept="image/*">
                            <label class="custom-file-label" for="resim">Dosya seçin</label>
                        </div>
                        <small class="form-text text-muted">JPG, JPEG, PNG veya WEBP formatında, maksimum 5MB büyüklüğünde.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Önizleme</label>
                        <div class="mt-2 img-preview text-center">
                            <img id="resimOnizleme" src="../images/no-image.jpg" alt="Önizleme" class="img-fluid img-thumbnail" style="max-height: 200px;">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kapasite">Standart Kapasite</label>
                                <input type="number" class="form-control" id="kapasite" name="kapasite" value="<?php echo isset($kapasite) ? $kapasite : 2; ?>" min="1" required>
                                <small class="form-text text-muted">Temel (normal) kişi kapasitesi</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_kapasite">Maksimum Kapasite</label>
                                <input type="number" class="form-control" id="max_kapasite" name="max_kapasite" value="<?php echo isset($max_kapasite) ? $max_kapasite : 2; ?>" min="1" required>
                                <small class="form-text text-muted">Maksimum (ek yatak ile) kişi kapasitesi</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="metrekare">Oda Büyüklüğü (m²)</label>
                                <input type="number" class="form-control" id="metrekare" name="metrekare" value="<?php echo isset($metrekare) ? $metrekare : 25; ?>" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stok">Toplam Oda Sayısı</label>
                                <input type="number" class="form-control" id="stok" name="stok" value="<?php echo isset($stok) ? $stok : 1; ?>" min="1" required>
                                <small class="form-text text-muted">Bu tipte kaç oda var?</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="taban_fiyat">Taban Fiyat (TL)</label>
                        <input type="number" class="form-control" id="taban_fiyat" name="taban_fiyat" value="<?php echo isset($taban_fiyat) ? $taban_fiyat : 500; ?>" min="0" step="0.01" required>
                        <small class="form-text text-muted">Standart kapasite için gecelik fiyat</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ek_yetiskin_fiyat">Ek Yetişkin Fiyatı</label>
                                <input type="number" class="form-control" id="ek_yetiskin_fiyat" name="ek_yetiskin_fiyat" value="<?php echo isset($ek_yetiskin_fiyat) ? $ek_yetiskin_fiyat : 150; ?>" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="buyuk_cocuk_fiyat">Büyük Çocuk Fiyatı</label>
                                <input type="number" class="form-control" id="buyuk_cocuk_fiyat" name="buyuk_cocuk_fiyat" value="<?php echo isset($buyuk_cocuk_fiyat) ? $buyuk_cocuk_fiyat : 100; ?>" min="0" step="0.01">
                                <small class="form-text text-muted">7-12 yaş</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kucuk_cocuk_fiyat">Küçük Çocuk Fiyatı</label>
                                <input type="number" class="form-control" id="kucuk_cocuk_fiyat" name="kucuk_cocuk_fiyat" value="<?php echo isset($kucuk_cocuk_fiyat) ? $kucuk_cocuk_fiyat : 0; ?>" min="0" step="0.01">
                                <small class="form-text text-muted">0-6 yaş</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="durum">Durum</label>
                                <select class="form-control" id="durum" name="durum">
                                    <option value="1" <?php echo (isset($durum) && $durum == 1) ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="0" <?php echo (isset($durum) && $durum == 0) ? 'selected' : ''; ?>>Pasif</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sira">Sıra No</label>
                                <input type="number" class="form-control" id="sira" name="sira" value="<?php echo isset($sira) ? $sira : $sira; ?>" min="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="aciklama">Oda Açıklaması</label>
                <textarea class="form-control summernote" id="aciklama" name="aciklama" rows="10"><?php echo isset($aciklama) ? $aciklama : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Oda Özellikleri</label>
                <div class="row">
                    <?php foreach ($ozellikler as $ozellik): ?>
                    <div class="col-md-3 mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ozellik_<?php echo $ozellik['id']; ?>" name="ozellikler[]" value="<?php echo $ozellik['id']; ?>" <?php echo (isset($secilen_ozellikler) && in_array($ozellik['id'], $secilen_ozellikler)) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="ozellik_<?php echo $ozellik['id']; ?>">
                                <i class="<?php echo $ozellik['icon']; ?> mr-1"></i> <?php echo $ozellik['adi']; ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-group text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg px-5">Oda Ekle</button>
                <a href="odalar.php" class="btn btn-secondary btn-lg px-5 ml-2">İptal</a>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Resim önizleme
    $('#resim').change(function() {
        var file = this.files[0];
        var reader = new FileReader();
        
        reader.onload = function(e) {
            $('#resimOnizleme').attr('src', e.target.result);
        }
        
        if (file) {
            reader.readAsDataURL(file);
            $('.custom-file-label').text(file.name);
        } else {
            $('#resimOnizleme').attr('src', '../images/no-image.jpg');
            $('.custom-file-label').text('Dosya seçin');
        }
    });
    
    // Summernote editör
    $('.summernote').summernote({
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'italic', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onImageUpload: function(files) {
                // Resim yükleme işlemi burada yapılabilir
                // Şimdilik basit bir uyarı gösterelim
                alert('Lütfen önce resmi sunucuya yükleyin, sonra URL olarak ekleyin.');
            }
        }
    });
    
    // Maksimum kapasite, standart kapasiteden küçük olamaz
    $('#kapasite').change(function() {
        var kapasite = parseInt($(this).val());
        var max_kapasite = parseInt($('#max_kapasite').val());
        
        if (max_kapasite < kapasite) {
            $('#max_kapasite').val(kapasite);
        }
    });
    
    $('#max_kapasite').change(function() {
        var kapasite = parseInt($('#kapasite').val());
        var max_kapasite = parseInt($(this).val());
        
        if (max_kapasite < kapasite) {
            $(this).val(kapasite);
            alert('Maksimum kapasite, standart kapasiteden küçük olamaz.');
        }
    });
});
</script>

<?php
// Footer kısmını dahil et
include 'include/footer.php';
?>bold text-primary">Oda Bilgileri</h6>
    </div>
    <div class="card-body">
        <form method="post" action="" enctype="multipart/form-data">
            <div class="row">
                <div