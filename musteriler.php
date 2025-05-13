<?php
session_start();
require_once '../include/config.php';
require_once '../include/db.php';
require_once '../include/functions.php';

// Sayfa başlığı
$sayfa_baslik = 'Müşteriler';

// Filtreler
$arama = isset($_GET['arama']) ? $_GET['arama'] : '';
$ulke = isset($_GET['ulke']) ? $_GET['ulke'] : '';
$tarih_baslangic = isset($_GET['tarih_baslangic']) ? $_GET['tarih_baslangic'] : '';
$tarih_bitis = isset($_GET['tarih_bitis']) ? $_GET['tarih_bitis'] : '';

// SQL sorgusu oluştur
$sql = "SELECT * FROM musteriler WHERE 1=1";
$params = [];

// Arama filtresi
if (!empty($arama)) {
    $sql .= " AND (
        adi LIKE ? OR 
        soyadi LIKE ? OR 
        email LIKE ? OR 
        telefon LIKE ?
    )";
    $arama_param = "%$arama%";
    $params[] = $arama_param;
    $params[] = $arama_param;
    $params[] = $arama_param;
    $params[] = $arama_param;
}

// Ülke filtresi
if (!empty($ulke)) {
    $sql .= " AND ulke = ?";
    $params[] = $ulke;
}

// Tarih filtresi
if (!empty($tarih_baslangic)) {
    $sql .= " AND kayit_tarihi >= ?";
    $params[] = tarihMySQL($tarih_baslangic) . ' 00:00:00';
}

if (!empty($tarih_bitis)) {
    $sql .= " AND kayit_tarihi <= ?";
    $params[] = tarihMySQL($tarih_bitis) . ' 23:59:59';
}

// Sıralama
$sql .= " ORDER BY kayit_tarihi DESC";

// Sorguyu çalıştır
$musteri_sorgu = $db->prepare($sql);
$musteri_sorgu->execute($params);
$musteriler = $musteri_sorgu->fetchAll();

// Ülke listesini getir
$ulke_sorgu = $db->query("SELECT DISTINCT ulke FROM musteriler WHERE ulke != '' ORDER BY ulke");
$ulkeler = $ulke_sorgu->fetchAll(PDO::FETCH_COLUMN);

// Header kısmını dahil et
include 'include/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Müşteriler</h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#exportModal">
        <i class="fas fa-download fa-sm text-white-50"></i> Rapor Oluştur
    </a>
</div>

<!-- Filtreler -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filtreleme</h6>
    </div>
    <div class="card-body">
        <form method="get" action="">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="arama">Arama</label>
                    <input type="text" class="form-control" id="arama" name="arama" value="<?php echo $arama; ?>" placeholder="Ad, soyad, e-posta, telefon...">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="ulke">Ülke</label>
                    <select class="form-control" id="ulke" name="ulke">
                        <option value="">Tümü</option>
                        <?php foreach ($ulkeler as $ulke_adi): ?>
                            <option value="<?php echo $ulke_adi; ?>" <?php echo ($ulke == $ulke_adi) ? 'selected' : ''; ?>><?php echo $ulke_adi; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="tarih_baslangic">Kayıt Tarihi (Başlangıç)</label>
                    <input type="text" class="form-control datepicker" id="tarih_baslangic" name="tarih_baslangic" value="<?php echo $tarih_baslangic; ?>" placeholder="GG.AA.YYYY">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="tarih_bitis">Kayıt Tarihi (Bitiş)</label>
                    <input type="text" class="form-control datepicker" id="tarih_bitis" name="tarih_bitis" value="<?php echo $tarih_bitis; ?>" placeholder="GG.AA.YYYY">
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-right">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrele</button>
                    <a href="musteriler.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Sıfırla</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- İstatistikler -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Toplam Müşteri</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($musteriler); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Son 30 Gün Yeni Müşteri</div>
                        <?php
                        $son30_sorgu = $db->query("SELECT COUNT(*) as sayi FROM musteriler WHERE kayit_tarihi >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                        $son30_musteri = $son30_sorgu->fetch(PDO::FETCH_COLUMN);
                        ?>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $son30_musteri; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Ortalama Rez. Sayısı/Müşteri</div>
                        <?php
                        $ort_sorgu = $db->query("
                            SELECT AVG(rezervasyon_sayisi) as ortalama FROM (
                                SELECT musteri_id, COUNT(*) as rezervasyon_sayisi
                                FROM rezervasyonlar
                                GROUP BY musteri_id
                            ) as rezervasyonlar
                        ");
                        $ortalama_rez = $ort_sorgu->fetch(PDO::FETCH_COLUMN);
                        ?>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($ortalama_rez, 1); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Rez. Olmayan Müşteriler</div>
                        <?php
                        $sifir_rez_sorgu = $db->query("
                            SELECT COUNT(*) as sayi FROM musteriler m
                            LEFT JOIN rezervasyonlar r ON m.id = r.musteri_id
                            WHERE r.id IS NULL
                        ");
                        $sifir_rez = $sifir_rez_sorgu->fetch(PDO::FETCH_COLUMN);
                        ?>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $sifir_rez; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Müşteriler Tablosu -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Müşteri Listesi</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                <div class="dropdown-header">İşlemler:</div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#exportModal">
                    <i class="fas fa-file-export fa-sm fa-fw mr-2 text-gray-400"></i> Dışa Aktar
                </a>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-file-import fa-sm fa-fw mr-2 text-gray-400"></i> İçe Aktar
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" id="topluEmailBtn">
                    <i class="fas fa-envelope fa-sm fa-fw mr-2 text-gray-400"></i> Toplu E-posta
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>Telefon</th>
                        <th>Şehir</th>
                        <th>Ülke</th>
                        <th>Kayıt Tarihi</th>
                        <th>Rezervasyon</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($musteriler as $musteri): ?>
                    <tr>
                        <td><?php echo $musteri['adi'] . ' ' . $musteri['soyadi']; ?></td>
                        <td><a href="mailto:<?php echo $musteri['email']; ?>"><?php echo $musteri['email']; ?></a></td>
                        <td><?php echo $musteri['telefon']; ?></td>
                        <td><?php echo $musteri['sehir']; ?></td>
                        <td><?php echo $musteri['ulke']; ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($musteri['kayit_tarihi'])); ?></td>
                        <td>
                            <?php
                            $rez_sayi_sorgu = $db->prepare("SELECT COUNT(*) as sayi FROM rezervasyonlar WHERE musteri_id = ?");
                            $rez_sayi_sorgu->execute([$musteri['id']]);
                            $rez_sayi = $rez_sayi_sorgu->fetch(PDO::FETCH_COLUMN);
                            
                            if ($rez_sayi > 0) {
                                echo '<a href="rezervasyonlar.php?musteri_id=' . $musteri['id'] . '" class="badge badge-primary">' . $rez_sayi . ' Rezervasyon</a>';
                            } else {
                                echo '<span class="badge badge-secondary">Rezervasyon Yok</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="musteri_detay.php?id=<?php echo $musteri['id']; ?>" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Detaylar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="musteri_duzenle.php?id=<?php echo $musteri['id']; ?>" class="btn btn-sm btn-warning" data-toggle="tooltip" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-info emailBtn" data-id="<?php echo $musteri['id']; ?>" data-email="<?php echo $musteri['email']; ?>" data-toggle="tooltip" title="E-posta Gönder">
                                    <i class="fas fa-envelope"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-danger silBtn" data-id="<?php echo $musteri['id']; ?>" data-toggle="tooltip" title="Sil">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Dışa Aktar Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Müşteri Listesi Dışa Aktar</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="exportForm" action="islemler/musteri_export.php" method="post">
                    <div class="form-group">
                        <label for="exportFormat">Format</label>
                        <select class="form-control" id="exportFormat" name="format">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Dışa Aktarılacak Alanlar</label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="exportAll" name="export_all" value="1" checked>
                            <label class="custom-control-label" for="exportAll">Tümünü Seç</label>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input export-field" id="exportField1" name="export_fields[]" value="id" checked>
                                    <label class="custom-control-label" for="exportField1">ID</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input export-field" id="exportField2" name="export_fields[]" value="adi" checked>
                                    <label class="custom-control-label" for="exportField2">Ad</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input export-field" id="exportField3" name="export_fields[]" value="soyadi" checked>
                                    <label class="custom-control-label" for="exportField3">Soyad</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input export-field" id="exportField4" name="export_fields[]" value="email" checked>
                                    <label class="custom-control-label" for="exportField4">E-posta</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input export-field" id="exportField5" name="export_fields[]" value="telefon" checked>
                                    <label class="custom-control-label" for="exportField5">Telefon</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input export-field" id="exportField6" name="export_fields[]" value="adres" checked>
                                    <label class="custom-control-label" for="exportField6">Adres</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input export-field" id="exportField7" name="export_fields[]" value="sehir" checked>
                                    <label class="custom-control-label" for="exportField7">Şehir</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input export-field" id="exportField8" name="export_fields[]" value="ulke" checked>
                                    <label class="custom-control-label" for="exportField8">Ülke</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input export-field" id="exportField9" name="export_fields[]" value="kayit_tarihi" checked>
                                    <label class="custom-control-label" for="exportField9">Kayıt Tarihi</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input export-field" id="exportField10" name="export_fields[]" value="rezervasyon_sayisi" checked>
                                    <label class="custom-control-label" for="exportField10">Rezervasyon Sayısı</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="arama" value="<?php echo $arama; ?>">
                    <input type="hidden" name="ulke" value="<?php echo $ulke; ?>">
                    <input type="hidden" name="tarih_baslangic" value="<?php echo $tarih_baslangic; ?>">
                    <input type="hidden" name="tarih_bitis" value="<?php echo $tarih_bitis; ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                <button class="btn btn-primary" id="exportBtn">Dışa Aktar</button>
            </div>
        </div>
    </div>
</div>

<!-- İçe Aktar Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Müşteri Listesi İçe Aktar</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importForm" action="islemler/musteri_import.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="importFile">Dosya Seçin</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="importFile" name="import_file" accept=".xlsx, .csv">
                            <label class="custom-file-label" for="importFile">Dosya seçilmedi</label>
                        </div>
                        <small class="form-text text-muted">Excel (.xlsx) veya CSV (.csv) formatında dosya yükleyebilirsiniz.</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="overwrite" name="overwrite" value="1">
                            <label class="custom-control-label" for="overwrite">Aynı e-posta adresine sahip müşterileri güncelle</label>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <p class="mb-0">Excel dosyasında şu sütunlar olmalıdır: Ad, Soyad, E-posta, Telefon, Adres, Şehir, Ülke</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                <button class="btn btn-primary" id="importBtn">İçe Aktar</button>
            </div>
        </div>
    </div>
</div>

<!-- E-posta Gönder Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailModalLabel">E-posta Gönder</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="emailForm" action="islemler/email_gonder.php" method="post">
                    <input type="hidden" name="musteri_id" id="musteri_id" value="">
                    <div class="form-group">
                        <label for="email_alici">Alıcı</label>
                        <input type="email" class="form-control" id="email_alici" name="alici" required>
                    </div>
                    <div class="form-group">
                        <label for="email_konu">Konu</label>
                        <input type="text" class="form-control" id="email_konu" name="konu" required>
                    </div>
                    <div class="form-group">
                        <label for="email_sablon">Şablon</label>
                        <select class="form-control" id="email_sablon" name="sablon">
                            <option value="">Özel Mesaj</option>
                            <option value="karsilama">Karşılama Mesajı</option>
                            <option value="kampanya">Kampanya Bildirimi</option>
                            <option value="dogum_gunu">Doğum Günü Tebriği</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="email_mesaj">Mesaj</label>
                        <textarea class="form-control summernote" id="email_mesaj" name="mesaj" rows="10"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                <button class="btn btn-primary" id="emailSendBtn">Gönder</button>
            </div>
        </div>
    </div>
</div>

<!-- Toplu E-posta Modal -->
<div class="modal fade" id="topluEmailModal" tabindex="-1" role="dialog" aria-labelledby="topluEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="topluEmailModalLabel">Toplu E-posta Gönder</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="topluEmailForm" action="islemler/toplu_email_gonder.php" method="post">
                    <div class="form-group">
                        <label>Alıcılar</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="alici_tum" name="alici_tipi" value="tum" class="custom-control-input" checked>
                            <label class="custom-control-label" for="alici_tum">Tüm Müşteriler</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="alici_filtre" name="alici_tipi" value="filtre" class="custom-control-input">
                            <label class="custom-control-label" for="alici_filtre">Aktif Filtreye Göre</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="alici_ozel" name="alici_tipi" value="ozel" class="custom-control-input">
                            <label class="custom-control-label" for="alici_ozel">Özel Liste</label>
                        </div>
                        
                        <div id="ozelListeDiv" class="mt-3" style="display: none;">
                            <select class="form-control select2" id="ozel_liste" name="ozel_liste[]" multiple="multiple">
                                <?php foreach ($musteriler as $musteri): ?>
                                <option value="<?php echo $musteri['id']; ?>"><?php echo $musteri['adi'] . ' ' . $musteri['soyadi'] . ' (' . $musteri['email'] . ')'; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <input type="hidden" name="arama" value="<?php echo $arama; ?>">
                        <input type="hidden" name="ulke" value="<?php echo $ulke; ?>">
                        <input type="hidden" name="tarih_baslangic" value="<?php echo $tarih_baslangic; ?>">
                        <input type="hidden" name="tarih_bitis" value="<?php echo $tarih_bitis; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="toplu_email_konu">Konu</label>
                        <input type="text" class="form-control" id="toplu_email_konu" name="konu" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="toplu_email_sablon">Şablon</label>
                        <select class="form-control" id="toplu_email_sablon" name="sablon">
                            <option value="">Özel Mesaj</option>
                            <option value="kampanya">Kampanya Bildirimi</option>
                            <option value="duyuru">Genel Duyuru</option>
                            <option value="ozel_teklif">Özel Teklif</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="toplu_email_mesaj">Mesaj</label>
                        <textarea class="form-control summernote" id="toplu_email_mesaj" name="mesaj" rows="10"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="test_gonder" name="test_gonder" value="1">
                            <label class="custom-control-label" for="test_gonder">Önce test e-postası gönder</label>
                        </div>
                        <div id="testEmailDiv" class="mt-2" style="display: none;">
                            <input type="email" class="form-control" id="test_email" name="test_email" placeholder="Test e-posta adresi">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                <button class="btn btn-primary" id="topluEmailSendBtn">Gönder</button>
            </div>
        </div>
    </div>
</div>

<!-- Silme Onay Modalı -->
<div class="modal fade" id="silModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Müşteri Silme</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Bu müşteriyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz ve müşteriye ait tüm rezervasyonlar da silinecektir.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                <a class="btn btn-danger" id="silLink" href="#">Sil</a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Dışa aktarma
    $('#exportBtn').click(function() {
        $('#exportForm').submit();
    });
    
    // Tüm alanları seç/kaldır
    $('#exportAll').change(function() {
        $('.export-field').prop('checked', this.checked);
    });
    
    $('.export-field').change(function() {
        if (!this.checked) {
            $('#exportAll').prop('checked', false);
        } else {
            // Tüm alanlar seçiliyse, "Tümünü Seç" i işaretle
            if ($('.export-field:checked').length === $('.export-field').length) {
                $('#exportAll').prop('checked', true);
            }
        }
    });
    
    // İçe aktarma
    $('#importBtn').click(function() {
        if ($('#importFile').val() === '') {
            alert('Lütfen bir dosya seçin.');
            return;
        }
        $('#importForm').submit();
    });
    
    // Dosya seçildiğinde dosya adını göster
    $('#importFile').change(function() {
        var fileName = $(this).val().split('\\').pop();
        $('.custom-file-label').text(fileName);
    });
    
    // E-posta gönder
    $('.emailBtn').click(function() {
        var musteri_id = $(this).data('id');
        var email = $(this).data('email');
        
        $('#musteri_id').val(musteri_id);
        $('#email_alici').val(email);
        $('#email_konu').val('');
        $('#email_mesaj').summernote('code', '');
        
        $('#emailModal').modal('show');
    });
    
    $('#emailSendBtn').click(function() {
        if ($('#email_alici').val() === '' || $('#email_konu').val() === '') {
            alert('Lütfen tüm alanları doldurun.');
            return;
        }
        $('#emailForm').submit();
    });
    
    // Şablon değişikliğinde içeriği doldur
    $('#email_sablon').change(function() {
        var sablon = $(this).val();
        var summernote = $('#email_mesaj').summernote();
        
        if (sablon === 'karsilama') {
            var icerik = '<p>Değerli Müşterimiz,</p>';
            icerik += '<p>Sitemize hoş geldiniz! Sizlere daha iyi hizmet verebilmek için tüm ekibimizle çalışıyoruz.</p>';
            icerik += '<p>Herhangi bir sorunuz veya talebiniz olursa bizimle iletişime geçmekten çekinmeyin.</p>';
            icerik += '<p>Saygılarımızla,<br>' + '<?php echo SITE_NAME; ?>' + ' Ekibi</p>';
            
            summernote.summernote('code', icerik);
            $('#email_konu').val('Hoş Geldiniz!');
        } else if (sablon === 'kampanya') {
            var icerik = '<p>Değerli Müşterimiz,</p>';
            icerik += '<p>Özel kampanyamızdan yararlanmak için bu fırsatı kaçırmayın!</p>';
            icerik += '<p>%20 indirim fırsatını kaçırmayın, rezervasyonunuzu hemen yapın!</p>';
            icerik += '<p>Saygılarımızla,<br>' + '<?php echo SITE_NAME; ?>' + ' Ekibi</p>';
            
            summernote.summernote('code', icerik);
            $('#email_konu').val('Özel Kampanya!');
        } else if (sablon === 'dogum_gunu') {
            var icerik = '<p>Değerli Müşterimiz,</p>';
            icerik += '<p>Doğum gününüzü en içten dileklerimizle kutlarız!</p>';
            icerik += '<p>Doğum gününüze özel %15 indirim kuponu hediyemizi kullanmak için rezervasyon sırasında "DGUNU" kodunu kullanabilirsiniz.</p>';
            icerik += '<p>Saygılarımızla,<br>' + '<?php echo SITE_NAME; ?>' + ' Ekibi</p>';
            
            summernote.summernote('code', icerik);
            $('#email_konu').val('Doğum Gününüz Kutlu Olsun!');
        }
    });
    
    // Toplu E-posta Gönder
    $('#topluEmailBtn').click(function() {
        $('#topluEmailModal').modal('show');
    });
    
    $('#test_gonder').change(function() {
        if (this.checked) {
            $('#testEmailDiv').show();
        } else {
            $('#testEmailDiv').hide();
        }
    });
    
    $('input[name="alici_tipi"]').change(function() {
        if ($(this).val() === 'ozel') {
            $('#ozelListeDiv').show();
        } else {
            $('#ozelListeDiv').hide();
        }
    });
    
    $('#topluEmailSendBtn').click(function() {
        if ($('#toplu_email_konu').val() === '') {
            alert('Lütfen e-posta konusunu girin.');
            return;
        }
        
        if ($('input[name="alici_tipi"]:checked').val() === 'ozel' && $('#ozel_liste').val().length === 0) {
            alert('Lütfen en az bir müşteri seçin.');
            return;
        }
        
        if ($('#test_gonder').is(':checked') && $('#test_email').val() === '') {
            alert('Lütfen test e-posta adresini girin.');
            return;
        }
        
        $('#topluEmailForm').submit();
    });
    
    // Toplu şablon değişikliğinde içeriği doldur
    $('#toplu_email_sablon').change(function() {
        var sablon = $(this).val();
        var summernote = $('#toplu_email_mesaj').summernote();
        
        if (sablon === 'kampanya') {
            var icerik = '<p>Değerli Müşterimiz,</p>';
            icerik += '<p>Özel kampanyamızdan yararlanmak için bu fırsatı kaçırmayın!</p>';
            icerik += '<p>%20 indirim fırsatını kaçırmayın, rezervasyonunuzu hemen yapın!</p>';
            icerik += '<p>Saygılarımızla,<br>' + '<?php echo SITE_NAME; ?>' + ' Ekibi</p>';
            
            summernote.summernote('code', icerik);
            $('#toplu_email_konu').val('Özel Kampanya!');
        } else if (sablon === 'duyuru') {
            var icerik = '<p>Değerli Müşterimiz,</p>';
            icerik += '<p>Otelimizde yaptığımız yenilikleri sizlerle paylaşmak istiyoruz:</p>';
            icerik += '<ul>';
            icerik += '<li>Yeni SPA merkezimiz hizmetinize açıldı</li>';
            icerik += '<li>Restoranımız yenilendi</li>';
            icerik += '<li>Yüzme havuzumuz artık 24 saat açık</li>';
            icerik += '</ul>';
            icerik += '<p>Saygılarımızla,<br>' + '<?php echo SITE_NAME; ?>' + ' Ekibi</p>';
            
            summernote.summernote('code', icerik);
            $('#toplu_email_konu').val('Otelimizde Yenilikler');
        } else if (sablon === 'ozel_teklif') {
            var icerik = '<p>Değerli Müşterimiz,</p>';
            icerik += '<p>Size özel hazırladığımız teklifi değerlendirmenizi rica ederiz:</p>';
            icerik += '<p>3 gece konaklama + kahvaltı + akşam yemeği dahil kişi başı 1500 TL!</p>';
            icerik += '<p>Bu özel fırsattan yararlanmak için rezervasyon yaparken "OZEL2023" promosyon kodunu kullanabilirsiniz.</p>';
            icerik += '<p>Saygılarımızla,<br>' + '<?php echo SITE_NAME; ?>' + ' Ekibi</p>';
            
            summernote.summernote('code', icerik);
            $('#toplu_email_konu').val('Size Özel Teklif');
        }
    });
    
    // Silme işlemi
    $('.silBtn').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#silLink').attr('href', 'islemler/musteri_sil.php?id=' + id);
        $('#silModal').modal('show');
    });
    
    // Select2 başlat
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Müşteri seçin',
        allowClear: true
    });
});
</script>

<?php
// Footer kısmını dahil et
include 'include/footer.php';
?>