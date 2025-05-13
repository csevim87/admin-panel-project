<?php
session_start();
require_once '../include/config.php';
require_once '../include/db.php';
require_once '../include/functions.php';

// Sayfa başlığı
$sayfa_baslik = 'Odalar';

// Filtreler
$durum = isset($_GET['durum']) ? intval($_GET['durum']) : -1;
$kapasite = isset($_GET['kapasite']) ? intval($_GET['kapasite']) : 0;
$arama = isset($_GET['arama']) ? $_GET['arama'] : '';

// SQL sorgusu oluştur
$sql = "SELECT * FROM odalar WHERE 1=1";
$params = [];

// Durum filtresi
if ($durum != -1) {
    $sql .= " AND durum = ?";
    $params[] = $durum;
}

// Kapasite filtresi
if ($kapasite > 0) {
    $sql .= " AND max_kapasite >= ?";
    $params[] = $kapasite;
}

// Arama filtresi
if (!empty($arama)) {
    $sql .= " AND (
        adi LIKE ? OR 
        kisa_aciklama LIKE ? OR
        aciklama LIKE ?
    )";
    $arama_param = "%$arama%";
    $params[] = $arama_param;
    $params[] = $arama_param;
    $params[] = $arama_param;
}

// Sıralama
$sql .= " ORDER BY sira ASC";

// Sorguyu çalıştır
$oda_sorgu = $db->prepare($sql);
$oda_sorgu->execute($params);
$odalar = $oda_sorgu->fetchAll();

// Header kısmını dahil et
include 'include/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Odalar</h1>
    <a href="oda_ekle.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Yeni Oda Ekle
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
                <div class="col-md-4 mb-3">
                    <label for="durum">Durum</label>
                    <select class="form-control" id="durum" name="durum">
                        <option value="-1">Tümü</option>
                        <option value="1" <?php echo ($durum === 1) ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?php echo ($durum === 0) ? 'selected' : ''; ?>>Pasif</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="kapasite">Minimum Kapasite</label>
                    <select class="form-control" id="kapasite" name="kapasite">
                        <option value="0">Tümü</option>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($kapasite == $i) ? 'selected' : ''; ?>><?php echo $i; ?> Kişi ve üzeri</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="arama">Arama</label>
                    <input type="text" class="form-control" id="arama" name="arama" value="<?php echo $arama; ?>" placeholder="Oda adı, açıklama...">
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-right">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrele</button>
                    <a href="odalar.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Sıfırla</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Odalar Tablosu -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Odalar</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                <div class="dropdown-header">İşlemler:</div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#topluSiralamaModal">
                    <i class="fas fa-sort fa-sm fa-fw mr-2 text-gray-400"></i> Toplu Sıralama
                </a>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#exportModal">
                    <i class="fas fa-file-export fa-sm fa-fw mr-2 text-gray-400"></i> Dışa Aktar
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" id="topluIslemBtn">
                    <i class="fas fa-tasks fa-sm fa-fw mr-2 text-gray-400"></i> Toplu İşlem
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form id="topluIslemForm" method="post" action="islemler/oda_toplu_islem.php">
            <div class="row mb-3 topluIslemRow" style="display: none;">
                <div class="col-md-4">
                    <select name="islem" class="form-control" required>
                        <option value="">İşlem Seçin</option>
                        <option value="aktif">Aktif Yap</option>
                        <option value="pasif">Pasif Yap</option>
                        <option value="sil">Sil</option>
                    </select>
                </div>
                <div class="col-md-8 text-right">
                    <button type="submit" class="btn btn-primary">Uygula</button>
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
                            <th style="width: 80px">Görsel</th>
                            <th>Oda Adı</th>
                            <th>Kapasite</th>
                            <th>Büyüklük</th>
                            <th>Taban Fiyat</th>
                            <th>Stok</th>
                            <th>Durum</th>
                            <th>Sıra</th>
                            <th style="width: 150px;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($odalar as $oda): ?>
                        <tr>
                            <td class="text-center topluIslemCol" style="display: none;">
                                <input type="checkbox" name="secilen_odalar[]" value="<?php echo $oda['id']; ?>" class="secim-checkbox">
                            </td>
                            <td class="text-center">
                                <?php if (!empty($oda['resim']) && file_exists('../images/odalar/' . $oda['resim'])): ?>
                                    <img src="../images/odalar/<?php echo $oda['resim']; ?>" alt="<?php echo $oda['adi']; ?>" class="img-thumbnail" width="60">
                                <?php else: ?>
                                    <img src="../images/no-image.jpg" alt="Resim yok" class="img-thumbnail" width="60">
                                <?php endif; ?>
                            </td>
                            <td><?php echo $oda['adi']; ?></td>
                            <td><?php echo $oda['kapasite']; ?> - <?php echo $oda['max_kapasite']; ?> kişi</td>
                            <td><?php echo $oda['metrekare']; ?> m²</td>
                            <td><?php echo fiyatFormat($oda['taban_fiyat']); ?></td>
                            <td><?php echo $oda['stok']; ?></td>
                            <td>
                                <?php if ($oda['durum'] == 1): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $oda['sira']; ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="oda_detay.php?id=<?php echo $oda['id']; ?>" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Detaylar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="oda_duzenle.php?id=<?php echo $oda['id']; ?>" class="btn btn-sm btn-warning" data-toggle="tooltip" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger silBtn" data-id="<?php echo $oda['id']; ?>" data-toggle="tooltip" title="Sil">
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

<!-- Toplu Sıralama Modal -->
<div class="modal fade" id="topluSiralamaModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Oda Sıralaması</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Odaların görüntülenme sırasını değiştirmek için sürükle-bırak yapabilirsiniz.</p>
                <form id="siralamaForm" method="post" action="islemler/oda_siralama.php">
                    <ul class="list-group sortable">
                        <?php 
                        // Tüm odaları sırayla getir
                        $sira_sorgu = $db->query("SELECT id, adi, sira FROM odalar ORDER BY sira ASC");
                        while ($sira_oda = $sira_sorgu->fetch()):
                        ?>
                        <li class="list-group-item d-flex align-items-center" data-id="<?php echo $sira_oda['id']; ?>">
                            <div class="handle mr-3"><i class="fas fa-grip-lines"></i></div>
                            <input type="hidden" name="oda_id[]" value="<?php echo $sira_oda['id']; ?>">
                            <?php echo $sira_oda['adi']; ?>
                            <span class="badge badge-primary ml-2"><?php echo $sira_oda['sira']; ?></span>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                <button class="btn btn-primary" id="siralamaKaydet">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<!-- Silme Onay Modalı -->
<div class="modal fade" id="silModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Oda Silme</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Bu odayı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.</div>
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
        $('#silLink').attr('href', 'islemler/oda_sil.php?id=' + id);
        $('#silModal').modal('show');
    });
    
    // Sıralama
    $('.sortable').sortable({
        handle: '.handle',
        update: function(event, ui) {
            // Sıralamaları güncelle
            $('.sortable li').each(function(index) {
                $(this).find('.badge').text(index + 1);
            });
        }
    });
    
    $('#siralamaKaydet').click(function() {
        $('#siralamaForm').submit();
    });
});
</script>

<?php
// Footer kısmını dahil et
include 'include/footer.php';
?>