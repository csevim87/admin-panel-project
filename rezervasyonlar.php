<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Rezervasyonlar</h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Rapor Oluştur</a>
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
                    <label for="durum">Durum</label>
                    <select class="form-control" id="durum" name="durum">
                        <option value="">Tümü</option>
                        <option value="bekliyor" <?php echo ($durum == 'bekliyor') ? 'selected' : ''; ?>>Bekliyor</option>
                        <option value="onaylandi" <?php echo ($durum == 'onaylandi') ? 'selected' : ''; ?>>Onaylandı</option>
                        <option value="iptal_edildi" <?php echo ($durum == 'iptal_edildi') ? 'selected' : ''; ?>>İptal Edildi</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="tarih_baslangic">Giriş Tarihi</label>
                    <input type="text" class="form-control datepicker" id="tarih_baslangic" name="tarih_baslangic" value="<?php echo $tarih_baslangic; ?>" placeholder="Başlangıç">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="tarih_bitis">Çıkış Tarihi</label>
                    <input type="text" class="form-control datepicker" id="tarih_bitis" name="tarih_bitis" value="<?php echo $tarih_bitis; ?>" placeholder="Bitiş">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="arama">Arama</label>
                    <input type="text" class="form-control" id="arama" name="arama" value="<?php echo $arama; ?>" placeholder="Rezervasyon no, Ad, Soyad...">
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-right">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrele</button>
                    <a href="rezervasyonlar.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Sıfırla</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Rezervasyonlar Tablosu -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Rezervasyonlar</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                <div class="dropdown-header">İşlemler:</div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#exportModal"><i class="fas fa-file-export fa-sm fa-fw mr-2 text-gray-400"></i> Dışa Aktar</a>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#importModal"><i class="fas fa-file-import fa-sm fa-fw mr-2 text-gray-400"></i> İçe Aktar</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" id="topluIslemBtn"><i class="fas fa-tasks fa-sm fa-fw mr-2 text-gray-400"></i> Toplu İşlem</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form id="topluIslemForm" method="post" action="">
            <div class="row mb-3 topluIslemRow" style="display: none;">
                <div class="col-md-4">
                    <select name="islem" class="form-control" required>
                        <option value="">İşlem Seçin</option>
                        <option value="onayla">Ödemeyi Onayla</option>
                        <option value="iptal">Rezervasyon İptal</option>
                    </select>
                </div>
                <div class="col-md-8 text-right">
                    <button type="submit" name="toplu_islem" class="btn btn-primary">Uygula</button>
                    <button type="button" class="btn btn-secondary" id="iptalBtn">İptal</button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="text-center topluIslemCol" style="display: none; width: 50px;">
                                <input type="checkbox" id="tumunuSec">
                            </th>
                            <th>Rezervasyon No</th>
                            <th>Müşteri</th>
                            <th>Oda</th>
                            <th>Giriş Tarihi</th>
                            <th>Çıkış Tarihi</th>
                            <th>Kişi</th>
                            <th>Toplam</th>
                            <th>Ödeme Yöntemi</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rezervasyonlar as $rez): ?>
                        <tr>
                            <td class="text-center topluIslemCol" style="display: none;">
                                <input type="checkbox" name="secilen_rezervasyonlar[]" value="<?php echo $rez['id']; ?>" class="secim-checkbox">
                            </td>
                            <td><?php echo $rez['rezervasyon_no']; ?></td>
                            <td><?php echo $rez['musteri_adi'] . ' ' . $rez['musteri_soyadi']; ?></td>
                            <td><?php echo $rez['oda_adi']; ?></td>
                            <td><?php echo tarihFormat($rez['giris_tarihi']); ?></td>
                            <td><?php echo tarihFormat($rez['cikis_tarihi']); ?></td>
                            <td><?php echo $rez['yetiskin'] + $rez['cocuk'] + $rez['buyuk_cocuk']; ?></td>
                            <td><?php echo fiyatFormat($rez['toplam_fiyat']); ?></td>
                            <td>
                                <?php 
                                switch ($rez['odeme_yontemi']) {
                                    case 'kredi_karti':
                                        echo '<span class="badge badge-primary">Kredi Kartı</span>';
                                        break;
                                    case 'havale':
                                        echo '<span class="badge badge-info">Havale</span>';
                                        break;
                                    case 'otelde_odeme':
                                        echo '<span class="badge badge-secondary">Otelde Ödeme</span>';
                                        break;
                                    default:
                                        echo $rez['odeme_yontemi'];
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                switch ($rez['odeme_durumu']) {
                                    case 'bekliyor':
                                        echo '<span class="badge badge-warning">Bekliyor</span>';
                                        break;
                                    case 'onaylandi':
                                        echo '<span class="badge badge-success">Onaylandı</span>';
                                        break;
                                    case 'iptal_edildi':
                                        echo '<span class="badge badge-danger">İptal</span>';
                                        break;
                                    default:
                                        echo $rez['odeme_durumu'];
                                }
                                ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="rezervasyon_detay.php?id=<?php echo $rez['id']; ?>" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Detaylar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="rezervasyon_duzenle.php?id=<?php echo $rez['id']; ?>" class="btn btn-sm btn-warning" data-toggle="tooltip" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger silBtn" data-id="<?php echo $rez['id']; ?>" data-toggle="tooltip" title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<!-- Dışa Aktar Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Dışa Aktar</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="exportFormat">Format</label>
                        <select class="form-control" id="exportFormat">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exportDateRange">Tarih Aralığı</label>
                        <div class="input-group">
                            <input type="text" class="form-control datepicker" id="exportStart" placeholder="Başlangıç">
                            <div class="input-group-prepend input-group-append">
                                <div class="input-group-text">-</div>
                            </div>
                            <input type="text" class="form-control datepicker" id="exportEnd" placeholder="Bitiş">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                <a class="btn btn-primary" href="#">Dışa Aktar</a>
            </div>
        </div>
    </div>
</div>

<!-- İçe Aktar Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">İçe Aktar</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="importFile">Dosya Seç</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="importFile">
                            <label class="custom-file-label" for="importFile">Dosya seçilmedi</label>
                        </div>
                        <small class="form-text text-muted">Excel (.xlsx) veya CSV (.csv) formatında olmalıdır.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                <a class="btn btn-primary" href="#">İçe Aktar</a>
            </div>
        </div>
    </div>
</div>

<!-- Silme Onay Modalı -->
<div class="modal fade" id="silModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Rezervasyon Silme</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Bu rezervasyonu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                <a class="btn btn-danger" id="silLink" href="#">Sil</a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toplu işlem
    $('#topluIslemBtn').click(function(e) {
        e.preventDefault();
        $('.topluIslemRow').toggle();
        $('.topluIslemCol').toggle();
    });
    
    $('#iptalBtn').click(function() {
        $('.topluIslemRow').hide();
        $('.topluIslemCol').hide();
        $('#tumunuSec').prop('checked', false);
        $('.secim-checkbox').prop('checked', false);
    });
    
    // Tümünü seç
    $('#tumunuSec').change(function() {
        $('.secim-checkbox').prop('checked', this.checked);
    });
    
    // Silme işlemi
    $('.silBtn').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#silLink').attr('href', 'islemler/rezervasyon_sil.php?id=' + id);
        $('#silModal').modal('show');
    });
    
    // Custom file input
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>

<?php
// Footer kısmını dahil et
include 'include/footer.php';
?><?php
session_start();
require_once '../include/config.php';
require_once '../include/db.php';
require_once '../include/functions.php';

// Sayfa başlığı
$sayfa_baslik = 'Rezervasyonlar';

// Filtreler
$durum = isset($_GET['durum']) ? $_GET['durum'] : '';
$tarih_baslangic = isset($_GET['tarih_baslangic']) ? $_GET['tarih_baslangic'] : '';
$tarih_bitis = isset($_GET['tarih_bitis']) ? $_GET['tarih_bitis'] : '';
$arama = isset($_GET['arama']) ? $_GET['arama'] : '';

// SQL sorgusu oluştur
$sql = "
    SELECT r.*, o.adi as oda_adi, m.adi as musteri_adi, m.soyadi as musteri_soyadi 
    FROM rezervasyonlar r
    INNER JOIN odalar o ON r.oda_id = o.id
    INNER JOIN musteriler m ON r.musteri_id = m.id
    WHERE 1=1
";

$params = [];

// Durum filtresi
if (!empty($durum)) {
    $sql .= " AND r.odeme_durumu = ?";
    $params[] = $durum;
}

// Tarih filtresi
if (!empty($tarih_baslangic)) {
    $sql .= " AND r.giris_tarihi >= ?";
    $params[] = tarihMySQL($tarih_baslangic);
}

if (!empty($tarih_bitis)) {
    $sql .= " AND r.cikis_tarihi <= ?";
    $params[] = tarihMySQL($tarih_bitis);
}

// Arama filtresi
if (!empty($arama)) {
    $sql .= " AND (
        r.rezervasyon_no LIKE ? OR 
        m.adi LIKE ? OR 
        m.soyadi LIKE ? OR 
        o.adi LIKE ?
    )";
    $arama_param = "%$arama%";
    $params[] = $arama_param;
    $params[] = $arama_param;
    $params[] = $arama_param;
    $params[] = $arama_param;
}

// Sıralama
$sql .= " ORDER BY r.olusturma_tarihi DESC";

// Sorguyu çalıştır
$rezervasyon_sorgu = $db->prepare($sql);
$rezervasyon_sorgu->execute($params);
$rezervasyonlar = $rezervasyon_sorgu->fetchAll();

// Toplu işlem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toplu_islem'])) {
    if (isset($_POST['secilen_rezervasyonlar']) && is_array($_POST['secilen_rezervasyonlar'])) {
        $secilen_rezervasyonlar = $_POST['secilen_rezervasyonlar'];
        $islem = $_POST['islem'];
        
        if (!empty($islem) && !empty($secilen_rezervasyonlar)) {
            switch ($islem) {
                case 'onayla':
                    $guncelle = $db->prepare("UPDATE rezervasyonlar SET odeme_durumu = 'onaylandi', guncelleme_tarihi = NOW() WHERE id = ?");
                    foreach ($secilen_rezervasyonlar as $id) {
                        $guncelle->execute([$id]);
                    }
                    break;
                
                case 'iptal':
                    $guncelle = $db->prepare("UPDATE rezervasyonlar SET odeme_durumu = 'iptal_edildi', guncelleme_tarihi = NOW() WHERE id = ?");
                    foreach ($secilen_rezervasyonlar as $id) {
                        $guncelle->execute([$id]);
                    }
                    break;
                
                default:
                    break;
            }
            
            // Sayfayı yenile
            header("Location: rezervasyonlar.php" . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
            exit;
        }
    }
}

// Header kısmını dahil et
include 'include/header.php';
?>