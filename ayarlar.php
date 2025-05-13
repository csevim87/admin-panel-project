<?php
session_start();
require_once '../include/config.php';
require_once '../include/db.php';
require_once '../include/functions.php';

// Admin kontrolü
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_yetki'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Sayfa başlığı
$sayfa_baslik = 'Site Ayarları';

// Ayarları getir (config.php dosyasından)
$site_url = SITE_URL;
$site_name = SITE_NAME;
$site_email = SITE_EMAIL;
$otel_email = OTEL_EMAIL;

// Ayarları güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guncelle'])) {
    $yeni_site_url = isset($_POST['site_url']) ? guvenliVeri($_POST['site_url']) : '';
    $yeni_site_name = isset($_POST['site_name']) ? guvenliVeri($_POST['site_name']) : '';
    $yeni_site_email = isset($_POST['site_email']) ? guvenliVeri($_POST['site_email']) : '';
    $yeni_otel_email = isset($_POST['otel_email']) ? guvenliVeri($_POST['otel_email']) : '';
    
    // Doğrulama
    $hatalar = [];
    
    if (empty($yeni_site_url)) {
        $hatalar[] = "Site URL boş olamaz.";
    }
    
    if (empty($yeni_site_name)) {
        $hatalar[] = "Site Adı boş olamaz.";
    }
    
    if (empty($yeni_site_email) || !filter_var($yeni_site_email, FILTER_VALIDATE_EMAIL)) {
        $hatalar[] = "Geçerli bir Site E-posta adresi giriniz.";
    }
    
    if (empty($yeni_otel_email) || !filter_var($yeni_otel_email, FILTER_VALIDATE_EMAIL)) {
        $hatalar[] = "Geçerli bir Otel E-posta adresi giriniz.";
    }
    
    // Hata yoksa güncelle
    if (empty($hatalar)) {
        // config.php dosyasını güncelle
        $config_file = '../include/config.php';
        $config_content = file_get_contents($config_file);
        
        // Değişkenleri güncelle
        $config_content = preg_replace('/define\(\'SITE_URL\', \'(.*?)\'\);/', "define('SITE_URL', '{$yeni_site_url}');", $config_content);
        $config_content = preg_replace('/define\(\'SITE_NAME\', \'(.*?)\'\);/', "define('SITE_NAME', '{$yeni_site_name}');", $config_content);
        $config_content = preg_replace('/define\(\'SITE_EMAIL\', \'(.*?)\'\);/', "define('SITE_EMAIL', '{$yeni_site_email}');", $config_content);
        $config_content = preg_replace('/define\(\'OTEL_EMAIL\', \'(.*?)\'\);/', "define('OTEL_EMAIL', '{$yeni_otel_email}');", $config_content);
        
        // Dosyaya yaz
        if (file_put_contents($config_file, $config_content)) {
            // Başarılı mesajı
            $basari_mesaj = "Site ayarları başarıyla güncellendi.";
            
            // Güncel değerleri al
            $site_url = $yeni_site_url;
            $site_name = $yeni_site_name;
            $site_email = $yeni_site_email;
            $otel_email = $yeni_otel_email;
        } else {
            $hata_mesaj = "Ayarlar kaydedilirken bir hata oluştu. config.php dosyasının yazma izni olduğundan emin olun.";
        }
    }
}

// Ödeme ayarlarını güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['odeme_guncelle'])) {
    $kredi_karti = isset($_POST['kredi_karti']) ? 1 : 0;
    $havale = isset($_POST['havale']) ? 1 : 0;
    $otelde = isset($_POST['otelde']) ? 1 : 0;
    
    $payment_api_key = isset($_POST['payment_api_key']) ? guvenliVeri($_POST['payment_api_key']) : '';
    $payment_secret_key = isset($_POST['payment_secret_key']) ? guvenliVeri($_POST['payment_secret_key']) : '';
    
    // Config dosyasını güncelle
    $config_file = '../include/config.php';
    $config_content = file_get_contents($config_file);
    
    // Değişkenleri güncelle
    $config_content = preg_replace('/define\(\'ODEME_KREDI_KARTI\', (.*?)\);/', "define('ODEME_KREDI_KARTI', {$kredi_karti});", $config_content);
    $config_content = preg_replace('/define\(\'ODEME_HAVALE\', (.*?)\);/', "define('ODEME_HAVALE', {$havale});", $config_content);
    $config_content = preg_replace('/define\(\'ODEME_OTELDE\', (.*?)\);/', "define('ODEME_OTELDE', {$otelde});", $config_content);
    
    $config_content = preg_replace('/define\(\'PAYMENT_API_KEY\', \'(.*?)\'\);/', "define('PAYMENT_API_KEY', '{$payment_api_key}');", $config_content);
    $config_content = preg_replace('/define\(\'PAYMENT_SECRET_KEY\', \'(.*?)\'\);/', "define('PAYMENT_SECRET_KEY', '{$payment_secret_key}');", $config_content);
    
    // Dosyaya yaz
    if (file_put_contents($config_file, $config_content)) {
        // Başarılı mesajı
        $odeme_basari = "Ödeme ayarları başarıyla güncellendi.";
    } else {
        $odeme_hata = "Ödeme ayarları kaydedilirken bir hata oluştu. config.php dosyasının yazma izni olduğundan emin olun.";
    }
}

// Header kısmını dahil et
include 'include/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Site Ayarları</h1>
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

<?php if (isset($basari_mesaj)): ?>
<div class="alert alert-success">
    <?php echo $basari_mesaj; ?>
</div>
<?php endif; ?>

<!-- Genel Ayarlar -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Genel Ayarlar</h6>
    </div>
    <div class="card-body">
        <form method="post" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="site_url">Site URL</label>
                        <input type="text" class="form-control" id="site_url" name="site_url" value="<?php echo $site_url; ?>" required>
                        <small class="form-text text-muted">Örn: http://localhost/otel-rezervasyon</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="site_name">Site Adı</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo $site_name; ?>" required>
                        <small class="form-text text-muted">Örn: Otel Rezervasyon Sistemi</small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="site_email">Site E-posta</label>
                        <input type="email" class="form-control" id="site_email" name="site_email" value="<?php echo $site_email; ?>" required>
                        <small class="form-text text-muted">Örn: info@otelrezervasyon.com</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="otel_email">Otel E-posta</label>
                        <input type="email" class="form-control" id="otel_email" name="otel_email" value="<?php echo $otel_email; ?>" required>
                        <small class="form-text text-muted">Örn: rezervasyon@otelrezervasyon.com</small>
                    </div>
                </div>
            </div>
            
            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary" name="guncelle">Ayarları Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Ödeme Ayarları -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Ödeme Ayarları</h6>
    </div>
    <div class="card-body">
        <?php if (isset($odeme_hata)): ?>
        <div class="alert alert-danger">
            <?php echo $odeme_hata; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($odeme_basari)): ?>
        <div class="alert alert-success">
            <?php echo $odeme_basari; ?>
        </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="kredi_karti" name="kredi_karti" value="1" <?php echo ODEME_KREDI_KARTI ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="kredi_karti">Kredi Kartı ile Ödeme</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="havale" name="havale" value="1" <?php echo ODEME_HAVALE ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="havale">Havale ile Ödeme</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="otelde" name="otelde" value="1" <?php echo ODEME_OTELDE ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="otelde">Otelde Ödeme</label>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Kredi Kartı API Ayarları</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_api_key">API Anahtarı</label>
                                <input type="text" class="form-control" id="payment_api_key" name="payment_api_key" value="<?php echo PAYMENT_API_KEY; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_secret_key">Gizli Anahtar</label>
                                <input type="password" class="form-control" id="payment_secret_key" name="payment_secret_key" value="<?php echo PAYMENT_SECRET_KEY; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="show_password">
                            <label class="custom-control-label" for="show_password">Gizli Anahtarı Göster</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary" name="odeme_guncelle">Ödeme Ayarlarını Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Veritabanı Yedekleme -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Veritabanı Yönetimi</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-0">
                    <div class="card-header">
                        <h6 class="mb-0">Veritabanı Yedekleme</h6>
                    </div>
                    <div class="card-body">
                        <p>Veritabanının şu anki durumunu yedekleyebilirsiniz. SQL dosyası olarak indirilebilir.</p>
                        <a href="islemler/db_backup.php" class="btn btn-success">Veritabanını Yedekle</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-0">
                    <div class="card-header">
                        <h6 class="mb-0">Veritabanı Optimizasyonu</h6>
                    </div>
                    <div class="card-body">
                        <p>Veritabanı tablolarını optimize edebilirsiniz. Bu işlem veritabanının performansını artırabilir.</p>
                        <a href="islemler/db_optimize.php" class="btn btn-primary">Veritabanını Optimize Et</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- E-posta Ayarları -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">E-posta Ayarları</h6>
    </div>
    <div class="card-body">
        <p class="mb-3">Bu bölümde e-posta şablonlarını düzenleyebilirsiniz. E-posta şablonları, otomatik olarak gönderilen e-postalarda kullanılır.</p>
        
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="rezervasyon-tab" data-toggle="tab" href="#rezervasyon" role="tab" aria-controls="rezervasyon" aria-selected="true">Rezervasyon Onayı</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="iptal-tab" data-toggle="tab" href="#iptal" role="tab" aria-controls="iptal" aria-selected="false">Rezervasyon İptali</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="degisiklik-tab" data-toggle="tab" href="#degisiklik" role="tab" aria-controls="degisiklik" aria-selected="false">Rezervasyon Değişikliği</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="hatirlatma-tab" data-toggle="tab" href="#hatirlatma" role="tab" aria-controls="hatirlatma" aria-selected="false">Giriş Hatırlatma</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="rezervasyon" role="tabpanel" aria-labelledby="rezervasyon-tab">
                <div class="p-3">
                    <form method="post" action="islemler/email_sablon_kaydet.php">
                        <input type="hidden" name="sablon_tipi" value="rezervasyon_onay">
                        <div class="form-group">
                            <label for="rezervasyon_onay_konu">E-posta Konusu</label>
                            <input type="text" class="form-control" id="rezervasyon_onay_konu" name="konu" value="Rezervasyon Onayı: #REZERVASYON_NO#">
                        </div>
                        <div class="form-group">
                            <label for="rezervasyon_onay_icerik">E-posta İçeriği</label>
                            <textarea class="form-control summernote" id="rezervasyon_onay_icerik" name="icerik" rows="10">
                                <p>Sayın #MUSTERI_ADI# #MUSTERI_SOYADI#,</p>
                                <p>Rezervasyonunuz başarıyla oluşturulmuştur. Rezervasyon detaylarınız aşağıdadır:</p>
                                <table>
                                    <tr>
                                        <th>Rezervasyon No</th>
                                        <td>#REZERVASYON_NO#</td>
                                    </tr>
                                    <tr>
                                        <th>Oda</th>
                                        <td>#ODA_ADI#</td>
                                    </tr>
                                    <tr>
                                        <th>Giriş Tarihi</th>
                                        <td>#GIRIS_TARIHI#</td>
                                    </tr>
                                    <tr>
                                        <th>Çıkış Tarihi</th>
                                        <td>#CIKIS_TARIHI#</td>
                                    </tr>
                                    <tr>
                                        <th>Misafir</th>
                                        <td>#MISAFIR_BILGISI#</td>
                                    </tr>
                                    <tr>
                                        <th>Toplam Tutar</th>
                                        <td>#TOPLAM_FIYAT#</td>
                                    </tr>
                                    <tr>
                                        <th>Ödeme Yöntemi</th>
                                        <td>#ODEME_YONTEMI#</td>
                                    </tr>
                                </table>
                                <p>Rezervasyonunuzu görüntülemek, değiştirmek veya iptal etmek için <a href="#REZERVASYON_LINK#">buraya tıklayabilirsiniz</a>.</p>
                                <p>Sorularınız veya talepleriniz için bizimle iletişime geçebilirsiniz.</p>
                                <p>Bizi tercih ettiğiniz için teşekkür ederiz.</p>
                                <p>Saygılarımızla,<br>#SITE_NAME# Ekibi</p>
                            </textarea>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Şablonu Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="iptal" role="tabpanel" aria-labelledby="iptal-tab">
                <div class="p-3">
                    <form method="post" action="islemler/email_sablon_kaydet.php">
                        <input type="hidden" name="sablon_tipi" value="rezervasyon_iptal">
                        <div class="form-group">
                            <label for="rezervasyon_iptal_konu">E-posta Konusu</label>
                            <input type="text" class="form-control" id="rezervasyon_iptal_konu" name="konu" value="Rezervasyon İptali: #REZERVASYON_NO#">
                        </div>
                        <div class="form-group">
                            <label for="rezervasyon_iptal_icerik">E-posta İçeriği</label>
                            <textarea class="form-control summernote" id="rezervasyon_iptal_icerik" name="icerik" rows="10">
                                <p>Sayın #MUSTERI_ADI# #MUSTERI_SOYADI#,</p>
                                <p><strong>#REZERVASYON_NO#</strong> numaralı rezervasyonunuz iptal edilmiştir.</p>
                                <p>İptal işlemi #IPTAL_TARIHI# tarihinde gerçekleştirilmiştir.</p>
                                <p>İptal nedeni: #IPTAL_NEDENI#</p>
                                <p>Sorularınız veya talepleriniz için bizimle iletişime geçebilirsiniz.</p>
                                <p>Saygılarımızla,<br>#SITE_NAME# Ekibi</p>
                            </textarea>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Şablonu Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="degisiklik" role="tabpanel" aria-labelledby="degisiklik-tab">
                <div class="p-3">
                    <form method="post" action="islemler/email_sablon_kaydet.php">
                        <input type="hidden" name="sablon_tipi" value="rezervasyon_degisiklik">
                        <div class="form-group">
                            <label for="rezervasyon_degisiklik_konu">E-posta Konusu</label>
                            <input type="text" class="form-control" id="rezervasyon_degisiklik_konu" name="konu" value="Rezervasyon Değişikliği: #REZERVASYON_NO#">
                        </div>
                        <div class="form-group">
                            <label for="rezervasyon_degisiklik_icerik">E-posta İçeriği</label>
                            <textarea class="form-control summernote" id="rezervasyon_degisiklik_icerik" name="icerik" rows="10">
                                <p>Sayın #MUSTERI_ADI# #MUSTERI_SOYADI#,</p>
                                <p><strong>#REZERVASYON_NO#</strong> numaralı rezervasyonunuzda değişiklik yapılmıştır. Güncel rezervasyon detaylarınız aşağıdadır:</p>
                                <table>
                                    <tr>
                                        <th>Rezervasyon No</th>
                                        <td>#REZERVASYON_NO#</td>
                                    </tr>
                                    <tr>
                                        <th>Oda</th>
                                        <td>#ODA_ADI#</td>
                                    </tr>
                                    <tr>
                                        <th>Giriş Tarihi</th>
                                        <td>#GIRIS_TARIHI#</td>
                                    </tr>
                                    <tr>
                                        <th>Çıkış Tarihi</th>
                                        <td>#CIKIS_TARIHI#</td>
                                    </tr>
                                    <tr>
                                        <th>Misafir</th>
                                        <td>#MISAFIR_BILGISI#</td>
                                    </tr>
                                    <tr>
                                        <th>Toplam Tutar</th>
                                        <td>#TOPLAM_FIYAT#</td>
                                    </tr>
                                </table>
                                <p>Değişiklik işlemi #DEGISIKLIK_TARIHI# tarihinde gerçekleştirilmiştir.</p>
                                <p>Sorularınız veya talepleriniz için bizimle iletişime geçebilirsiniz.</p>
                                <p>Saygılarımızla,<br>#SITE_NAME# Ekibi</p>
                            </textarea>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Şablonu Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="hatirlatma" role="tabpanel" aria-labelledby="hatirlatma-tab">
                <div class="p-3">
                    <form method="post" action="islemler/email_sablon_kaydet.php">
                        <input type="hidden" name="sablon_tipi" value="giris_hatirlatma">
                        <div class="form-group">
                            <label for="giris_hatirlatma_konu">E-posta Konusu</label>
                            <input type="text" class="form-control" id="giris_hatirlatma_konu" name="konu" value="Giriş Hatırlatma: #REZERVASYON_NO#">
                        </div>
                        <div class="form-group">
                            <label for="giris_hatirlatma_icerik">E-posta İçeriği</label>
                            <textarea class="form-control summernote" id="giris_hatirlatma_icerik" name="icerik" rows="10">
                                <p>Sayın #MUSTERI_ADI# #MUSTERI_SOYADI#,</p>
                                <p><strong>#REZERVASYON_NO#</strong> numaralı rezervasyonunuz için giriş tarihiniz yaklaşıyor.</p>
                                <p><strong>Giriş Tarihi:</strong> #GIRIS_TARIHI#<br>
                                <strong>Çıkış Tarihi:</strong> #CIKIS_TARIHI#<br>
                                <strong>Oda:</strong> #ODA_ADI#</p>
                                <p>Giriş işlemleri için lütfen kimlik belgelerinizi yanınızda bulundurunuz ve check-in saatimizin 14:00 olduğunu unutmayınız.</p>
                                <p>Herhangi bir sorunuz olursa bizimle iletişime geçebilirsiniz.</p>
                                <p>Bizi tercih ettiğiniz için teşekkür ederiz.</p>
                                <p>Saygılarımızla,<br>#SITE_NAME# Ekibi</p>
                            </textarea>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Şablonu Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Şifre göster/gizle
    $('#show_password').change(function() {
        if ($(this).is(':checked')) {
            $('#payment_secret_key').attr('type', 'text');
        } else {
            $('#payment_secret_key').attr('type', 'password');
        }
    });
});
</script>

<?php
// Footer kısmını dahil et
include 'include/footer.php';
?>