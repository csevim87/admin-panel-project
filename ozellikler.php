<?php
session_start();
require_once '../include/config.php';
require_once '../include/db.php';
require_once '../include/functions.php';

// Sayfa başlığı
$sayfa_baslik = 'Özellikler';

// Özellikleri getir
$ozellik_sorgu = $db->query("SELECT * FROM ozellikler ORDER BY sira ASC");
$ozellikler = $ozellik_sorgu->fetchAll();

// Ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {
    $adi = isset($_POST['adi']) ? guvenliVeri($_POST['adi']) : '';
    $icon = isset($_POST['icon']) ? guvenliVeri($_POST['icon']) : '';
    $aciklama = isset($_POST['aciklama']) ? guvenliVeri($_POST['aciklama']) : '';
    $durum = isset($_POST['durum']) ? intval($_POST['durum']) : 1;
    $sira = isset($_POST['sira']) ? intval($_POST['sira']) : 0;
    
    // Doğrulama
    $hatalar = [];
    
    if (empty($adi)) {
        $hatalar[] = "Özellik adı boş bırakılamaz.";
    }
    
    if (empty($icon)) {
        $hatalar[] = "İkon seçilmelidir.";
    }
    
    // Hata yoksa ekle
    if (empty($hatalar)) {
        $ekle_sorgu = $db->prepare("
            INSERT INTO ozellikler (adi, icon, aciklama, durum, sira)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $ekle_sorgu->execute([$adi, $icon, $aciklama, $durum, $sira]);
        
        // Başarıyla eklendiyse sayfayı yenile
        if ($ekle_sorgu->rowCount() > 0) {
            header("Location: ozellikler.php?durum=eklendi");
            exit;
        } else {
            $ekle_hata = "Özellik eklenirken bir hata oluştu.";
        }
    }
}

// Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guncelle'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $adi = isset($_POST['adi']) ? guvenliVeri($_POST['adi']) : '';
    $icon = isset($_POST['icon']) ? guvenliVeri($_POST['icon']) : '';
    $aciklama = isset($_POST['aciklama']) ? guvenliVeri($_POST['aciklama']) : '';
    $durum = isset($_POST['durum']) ? intval($_POST['durum']) : 1;
    $sira = isset($_POST['sira']) ? intval($_POST['sira']) : 0;
    
    // Doğrulama
    $guncelle_hatalar = [];
    
    if ($id <= 0) {
        $guncelle_hatalar[] = "Geçersiz özellik ID.";
    }
    
    if (empty($adi)) {
        $guncelle_hatalar[] = "Özellik adı boş bırakılamaz.";
    }
    
    if (empty($icon)) {
        $guncelle_hatalar[] = "İkon seçilmelidir.";
    }
    
    // Hata yoksa güncelle
    if (empty($guncelle_hatalar)) {
        $guncelle_sorgu = $db->prepare("
            UPDATE ozellikler 
            SET adi = ?, icon = ?, aciklama = ?, durum = ?, sira = ?
            WHERE id = ?
        ");
        
        $guncelle_sorgu->execute([$adi, $icon, $aciklama, $durum, $sira, $id]);
        
        // Başarıyla güncellendiyse sayfayı yenile
        if ($guncelle_sorgu->rowCount() >= 0) {
            header("Location: ozellikler.php?durum=guncellendi");
            exit;
        } else {
            $guncelle_hata = "Özellik güncellenirken bir hata oluştu.";
        }
    }
}

// Silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $silinecek_id = intval($_GET['sil']);
    
    // Önce bağlı oda özelliklerini sil
    $db->prepare("DELETE FROM oda_ozellikler WHERE ozellik_id = ?")->execute([$silinecek_id]);
    
    // Sonra özelliği sil
    $sil_sorgu = $db->prepare("DELETE FROM ozellikler WHERE id = ?");
    $sil_sorgu->execute([$silinecek_id]);
    
    if ($sil_sorgu->rowCount() > 0) {
        header("Location: ozellikler.php?durum=silindi");
        exit;
    } else {
        $sil_hata = "Özellik silinirken bir hata oluştu.";
    }
}

// Font Awesome ikonları
$font_awesome_icons = [
    'fas fa-wifi', 'fas fa-snowflake', 'fas fa-tv', 'fas fa-coffee', 'fas fa-utensils',
    'fas fa-swimming-pool', 'fas fa-hot-tub', 'fas fa-parking', 'fas fa-smoking-ban',
    'fas fa-concierge-bell', 'fas fa-bed', 'fas fa-bath', 'fas fa-shower', 'fas fa-couch',
    'fas fa-door-open', 'fas fa-window-maximize', 'fas fa-thermometer-half', 'fas fa-fan',
    'fas fa-wind', 'fas fa-glass-martini', 'fas fa-glass-cheers', 'fas fa-dumbbell',
    'fas fa-spa', 'fas fa-water', 'fas fa-map-marked-alt', 'fas fa-mountain', 'fas fa-umbrella-beach'
];

// En son sıra numarasını al
$son_sira_sorgu = $db->query("SELECT MAX(sira) as son_sira FROM ozellikler");
$son_sira = $son_sira_sorgu->fetch()['son_sira'];
$yeni_sira = $son_sira + 1;

// Header kısmını dahil et
include 'include/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Özellikler</h1>
    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#ekleModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Yeni Özellik Ekle
    </button>
</div>

<?php if (isset($_GET['durum'])): ?>
    <?php if ($_GET['durum'] == 'eklendi'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Özellik başarıyla eklendi.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php elseif ($_GET['durum'] == 'guncellendi'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Özellik başarıyla güncellendi.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php elseif ($_GET['durum'] == 'silindi'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Özellik başarıyla silindi.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (isset($hatalar) && !empty($hatalar)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach ($hatalar as $hata): ?>
                <li><?php echo $hata; ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($ekle_hata)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $ekle_hata; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($guncelle_hatalar) && !empty($guncelle_hatalar)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach ($guncelle_hatalar as $hata): ?>
                <li><?php echo $hata; ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($guncelle_hata)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $guncelle_hata; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($sil_hata)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $sil_hata; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Özellikler Listesi -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Özellikler Listesi</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                <div class="dropdown-header">İşlemler:</div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#siralamaModal">
                    <i class="fas fa-sort fa-sm fa-fw mr-2 text-gray-400"></i> Toplu Sıralama
                </a>
                <a class="dropdown-item" href="#" id="topluDurumBtn">
                    <i class="fas fa-toggle-on fa-sm fa-fw mr-2 text-gray-400"></i> Toplu Durum Değiştir
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 50px;">İkon</th>
                        <th>Özellik Adı</th>
                        <th>Açıklama</th>
                        <th style="width: 80px;">Sıra</th>
                        <th style="width: 80px;">Durum</th>
                        <th style="width: 150px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ozellikler as $ozellik): ?>
                    <tr>
                        <td><?php echo $ozellik['id']; ?></td>
                        <td class="text-center"><i class="<?php echo $ozellik['icon']; ?>"></i></td>
                        <td><?php echo $ozellik['adi']; ?></td>
                        <td><?php echo $ozellik['aciklama']; ?></td>
                        <td class="text-center"><?php echo $ozellik['sira']; ?></td>
                        <td class="text-center">
                            <?php if ($ozellik['durum'] == 1): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning duzenleBtn" data-toggle="modal" data-target="#duzenleModal" 
                                data-id="<?php echo $ozellik['id']; ?>" 
                                data-adi="<?php echo $ozellik['adi']; ?>" 
                                data-icon="<?php echo $ozellik['icon']; ?>" 
                                data-aciklama="<?php echo $ozellik['aciklama']; ?>" 
                                data-durum="<?php echo $ozellik['durum']; ?>" 
                                data-sira="<?php echo $ozellik['sira']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="#" class="btn btn-sm btn-danger silBtn" data-id="<?php echo $ozellik['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Özellik Ekleme Modal -->
<div class="modal fade" id="ekleModal" tabindex="-1" role="dialog" aria-labelledby="ekleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="ekleModalLabel">Yeni Özellik Ekle</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="adi">Özellik Adı</label>
                        <input type="text" class="form-control" id="adi" name="adi" value="<?php echo isset($adi) ? $adi : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="icon">İkon</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i id="icon-preview" class="<?php echo isset($icon) ? $icon : 'fas fa-wifi'; ?>"></i></span>
                            </div>
                            <select class="form-control" id="icon" name="icon" required>
                                <?php foreach ($font_awesome_icons as $fa_icon): ?>
                                    <option value="<?php echo $fa_icon; ?>" <?php echo (isset($icon) && $icon == $fa_icon) ? 'selected' : ''; ?> data-icon="<?php echo $fa_icon; ?>"><?php echo $fa_icon; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <small class="form-text text-muted">İkon seçmek için aşağıdaki listeden seçim yapabilirsiniz</small>
                    </div>
                    
                    <div class="form-group">
                        <label>İkon Seçenekleri</label>
                        <div class="icon-list p-3 border rounded">
                            <?php foreach ($font_awesome_icons as $fa_icon): ?>
                                <button type="button" class="btn btn-outline-secondary icon-btn m-1" data-icon="<?php echo $fa_icon; ?>">
                                    <i class="<?php echo $fa_icon; ?>"></i>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="aciklama">Açıklama</label>
                        <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?php echo isset($aciklama) ? $aciklama : ''; ?></textarea>
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
                                <input type="number" class="form-control" id="sira" name="sira" value="<?php echo isset($sira) ? $sira : $yeni_sira; ?>" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary" name="ekle">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Özellik Düzenleme Modal -->
<div class="modal fade" id="duzenleModal" tabindex="-1" role="dialog" aria-labelledby="duzenleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="id" id="duzenle_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="duzenleModalLabel">Özellik Düzenle</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="duzenle_adi">Özellik Adı</label>
                        <input type="text" class="form-control" id="duzenle_adi" name="adi" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="duzenle_icon">İkon</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i id="duzenle-icon-preview" class="fas fa-wifi"></i></span>
                            </div>
                            <select class="form-control" id="duzenle_icon" name="icon" required>
                                <?php foreach ($font_awesome_icons as $fa_icon): ?>
                                    <option value="<?php echo $fa_icon; ?>" data-icon="<?php echo $fa_icon; ?>"><?php echo $fa_icon; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <small class="form-text text-muted">İkon seçmek için aşağıdaki listeden seçim yapabilirsiniz</small>
                    </div>
                    
                    <div class="form-group">
                        <label>İkon Seçenekleri</label>
                        <div class="icon-list p-3 border rounded">
                            <?php foreach ($font_awesome_icons as $fa_icon): ?>
                                <button type="button" class="btn btn-outline-secondary duzenle-icon-btn m-1" data-icon="<?php echo $fa_icon; ?>">
                                    <i class="<?php echo $fa_icon; ?>"></i>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="duzenle_aciklama">Açıklama</label>
                        <textarea class="form-control" id="duzenle_aciklama" name="aciklama" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="duzenle_durum">Durum</label>
                                <select class="form-control" id="duzenle_durum" name="durum">
                                    <option value="1">Aktif</option>
                                    <option value="0">Pasif</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="duzenle_sira">Sıra No</label>
                                <input type="number" class="form-control" id="duzenle_sira" name="sira" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary" name="guncelle">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sıralama Modal -->
<div class="modal fade" id="siralamaModal" tabindex="-1" role="dialog" aria-labelledby="siralamaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="siralamaModalLabel">Özellikleri Sırala</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Özelliklerin görüntülenme sırasını değiştirmek için sürükle-bırak yapabilirsiniz.</p>
                <form id="siralamaForm" method="post" action="islemler/ozellik_siralama.php">
                    <ul class="list-group sortable">
                        <?php 
                        // Tüm özellikleri sırayla getir
                        $sira_sorgu = $db->query("SELECT id, adi, sira FROM ozellikler ORDER BY sira ASC");
                        while ($sira_ozellik = $sira_sorgu->fetch()):
                        ?>
                        <li class="list-group-item d-flex align-items-center" data-id="<?php echo $sira_ozellik['id']; ?>">
                            <div class="handle mr-3"><i class="fas fa-grip-lines"></i></div>
                            <input type="hidden" name="ozellik_id[]" value="<?php echo $sira_ozellik['id']; ?>">
                            <?php echo $sira_ozellik['adi']; ?>
                            <span class="badge badge-primary ml-2"><?php echo $sira_ozellik['sira']; ?></span>
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
<div class="modal fade" id="silModal" tabindex="-1" role="dialog" aria-labelledby="silModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="silModalLabel">Özellik Silme</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Bu özelliği silmek istediğinize emin misiniz? Bu işlem geri alınamaz ve bu özellik ile ilişkilendirilmiş oda özellikleri de silinecektir.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                <a class="btn btn-danger" id="silLink" href="#">Sil</a>
            </div>
        </div>
    </div>
</div>

<style>
.icon-list {
    max-height: 150px;
    overflow-y: auto;
}

.icon-btn, .duzenle-icon-btn {
    width: 40px;
    height: 40px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.icon-btn.active, .duzenle-icon-btn.active {
    background-color: #4e73df;
    color: white;
    border-color: #4e73df;
}

.sortable .handle {
    cursor: move;
}
</style>

<script>
$(document).ready(function() {
    // İkon seçme (Ekle Modal)
    $('.icon-btn').click(function() {
        var icon = $(this).data('icon');
        $('#icon').val(icon).trigger('change');
        $('.icon-btn').removeClass('active');
        $(this).addClass('active');
    });
    
    $('#icon').change(function() {
        var selectedIcon = $(this).val();
        $('#icon-preview').attr('class', selectedIcon);
        $('.icon-btn').removeClass('active');
        $('.icon-btn[data-icon="' + selectedIcon + '"]').addClass('active');
    });
    
    // İkon seçme (Düzenle Modal)
    $('.duzenle-icon-btn').click(function() {
        var icon = $(this).data('icon');
        $('#duzenle_icon').val(icon).trigger('change');
        $('.duzenle-icon-btn').removeClass('active');
        $(this).addClass('active');
    });
    
    $('#duzenle_icon').change(function() {
        var selectedIcon = $(this).val();
        $('#duzenle-icon-preview').attr('class', selectedIcon);
        $('.duzenle-icon-btn').removeClass('active');
        $('.duzenle-icon-btn[data-icon="' + selectedIcon + '"]').addClass('active');
    });
    
    // Düzenleme modalını aç
    $('.duzenleBtn').click(function() {
        var id = $(this).data('id');
        var adi = $(this).data('adi');
        var icon = $(this).data('icon');
        var aciklama = $(this).data('aciklama');
        var durum = $(this).data('durum');
        var sira = $(this).data('sira');
        
        $('#duzenle_id').val(id);
        $('#duzenle_adi').val(adi);
        $('#duzenle_icon').val(icon).trigger('change');
        $('#duzenle_aciklama').val(aciklama);
        $('#duzenle_durum').val(durum);
        $('#duzenle_sira').val(sira);
    });
    
    // Silme işlemi
    $('.silBtn').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#silLink').attr('href', 'ozellikler.php?sil=' + id);
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
?><?php
session_start();
require_once '../include/config.php';
require_once '../include/db.php';
require_once '../include/functions.php';

// Sayfa başlığı
$sayfa_baslik = 'Özellikler';

// Özellikleri getir
$ozellik_sorgu = $db->query("SELECT * FROM ozellikler ORDER BY sira ASC");
$ozellikler = $ozellik_sorgu->fetchAll();

// Ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {
    $adi = isset($_POST['adi']) ? guvenliVeri($_POST['adi']) : '';
    $icon = isset($_POST['icon']) ? guvenliVeri($_POST['icon']) : '';
    $aciklama = isset($_POST['aciklama']) ? guvenliVeri($_POST['aciklama']) : '';
    $durum = isset($_POST['durum']) ? intval($_POST['durum']) : 1;
    $sira = isset($_POST['sira']) ? intval($_POST['sira']) : 0;
    
    // Doğrulama
    $hatalar = [];
    
    if (empty($adi)) {
        $hatalar[] = "Özellik adı boş bırakılamaz.";
    }
    
    if (empty($icon)) {
        $hatalar[] = "İkon seçilmelidir.";
    }
    
    // Hata yoksa ekle
    if (empty($hatalar)) {
        $ekle_sorgu = $db->prepare("
            INSERT INTO ozellikler (adi, icon, aciklama, durum, sira)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $ekle_sorgu->execute([$adi, $icon, $aciklama, $durum, $sira]);
        
        // Başarıyla eklendiyse sayfayı yenile
        if ($ekle_sorgu->rowCount() > 0) {
            header("Location: ozellikler.php?durum=eklendi");
            exit;
        } else {
            $ekle_hata = "Özellik eklenirken bir hata oluştu.";
        }
    }
}

// Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guncelle'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $adi = isset($_POST['adi']) ? guvenliVeri($_POST['adi']) : '';
    $icon = isset($_POST['icon']) ? guvenliVeri($_POST['icon']) : '';
    $aciklama = isset($_POST['aciklama']) ? guvenliVeri($_POST['aciklama']) : '';
    $durum = isset($_POST['durum']) ? intval($_POST['durum']) : 1;
    $sira = isset($_POST['sira']) ? intval($_POST['sira']) : 0;
    
    // Doğrulama
    $guncelle_hatalar = [];
    
    if ($id <= 0) {
        $guncelle_hatalar[] = "Geçersiz özellik ID.";
    }
    
    if (empty($adi)) {
        $guncelle_hatalar[] = "Özellik adı boş bırakılamaz.";
    }
    
    if (empty($icon)) {
        $guncelle_hatalar[] = "İkon seçilmelidir.";
    }
    
    // Hata yoksa güncelle
    if (empty($guncelle_hatalar)) {
        $guncelle_sorgu = $db->prepare("
            UPDATE ozellikler 
            SET adi = ?, icon = ?, aciklama = ?, durum = ?, sira = ?
            WHERE id = ?
        ");
        
        $guncelle_sorgu->execute([$adi, $icon, $aciklama, $durum, $sira, $id]);
        
        // Başarıyla güncellendiyse sayfayı yenile
        if ($guncelle_sorgu->rowCount() >= 0) {
            header("Location: ozellikler.php?durum=guncellendi");
            exit;
        } else {
            $guncelle_hata = "Özellik güncellenirken bir hata oluştu.";
        }
    }
}

// Silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $silinecek_id = intval($_GET['sil']);
    
    // Önce bağlı oda özelliklerini sil
    $db->prepare("DELETE FROM oda_ozellikler WHERE ozellik_id = ?")->execute([$silinecek_id]);
    
    // Sonra özelliği sil
    $sil_sorgu = $db->prepare("DELETE FROM ozellikler WHERE id = ?");
    $sil_sorgu->execute([$silinecek_id]);
    
    if ($sil_sorgu->rowCount() > 0) {
        header("Location: ozellikler.php?durum=silindi");
        exit;
    } else {
        $sil_hata = "Özellik silinirken bir hata oluştu.";
    }
}

// Font Awesome ikonları
$font_awesome_icons = [
    'fas fa-wifi', 'fas fa-snowflake', 'fas fa-tv', 'fas fa-coffee', 'fas fa-utensils',
    'fas fa-swimming-pool', 'fas fa-hot-tub', 'fas fa-parking', 'fas fa-smoking-ban',
    'fas fa-concierge-bell', 'fas fa-bed', 'fas fa-bath', 'fas fa-shower', 'fas fa-couch',
    'fas fa-door-open', 'fas fa-window-maximize', 'fas fa-thermometer-half', 'fas fa-fan',
    'fas fa-wind', 'fas fa-glass-martini', 'fas fa-glass-cheers', 'fas fa-dumbbell',
    'fas fa-spa', 'fas fa-water', 'fas fa-map-marked-alt', 'fas fa-mountain', 'fas fa-umbrella-beach'
];

// En son sıra numarasını al
$son_sira_sorgu = $db->query("SELECT MAX(sira) as son_sira FROM ozellikler");
$son_sira = $son_sira_sorgu->fetch()['son_sira'];
$yeni_sira = $son_sira + 1;

// Header kısmını dahil et
include 'include/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Özellikler</h1>
    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#ekleModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Yeni Özellik Ekle
    </button>
</div>

<?php if (isset($_GET['durum'])): ?>
    <?php if ($_GET['durum'] == 'eklendi'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Özellik başarıyla eklendi.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php elseif ($_GET['durum'] == 'guncellendi'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Özellik başarıyla güncellendi.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php elseif ($_GET['durum'] == 'silindi'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Özellik başarıyla silindi.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (isset($hatalar) && !empty($hatalar)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach ($hatalar as $hata): ?>
                <li><?php echo $hata; ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($ekle_hata)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $ekle_hata; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($guncelle_hatalar) && !empty($guncelle_hatalar)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach ($guncelle_hatalar as $hata): ?>
                <li><?php echo $hata; ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($guncelle_hata)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $guncelle_hata; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($sil_hata)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $sil_hata; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Özellikler Listesi -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Özellikler Listesi</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                <div class="dropdown-header">İşlemler:</div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#siralamaModal">
                    <i class="fas fa-sort fa-sm fa-fw mr-2 text-gray-400"></i> Toplu Sıralama
                </a>
                <a class="dropdown-item" href="#" id="topluDurumBtn">
                    <i class="fas fa-toggle-on fa-sm fa-fw mr-2 text-gray-400"></i> Toplu Durum Değiştir
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 50px;">İkon</th>
                        <th>Özellik Adı</th>
                        <th>Açıklama</th>
                        <th style="width: 80px;">Sıra</th>
                        <th style="width: 80px;">Durum</th>
                        <th style="width: 150px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ozellikler as $ozellik): ?>
                    <tr>
                        <td><?php echo $ozellik['id']; ?></td>
                        <td class="text-center"><i class="<?php echo $ozellik['icon']; ?>"></i></td>
                        <td><?php echo $ozellik['adi']; ?></td>
                        <td><?php echo $ozellik['aciklama']; ?></td>
                        <td class="text-center"><?php echo $ozellik['sira']; ?></td>
                        <td class="text-center">
                            <?php if ($ozellik['durum'] == 1): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning duzenleBtn" data-toggle="modal" data-target="#duzenleModal" 
                                data-id="<?php echo $ozellik['id']; ?>" 
                                data-adi="<?php echo $ozellik['adi']; ?>" 
                                data-icon="<?php echo $ozellik['icon']; ?>" 
                                data-aciklama="<?php echo $ozellik['aciklama']; ?>" 
                                data-durum="<?php echo $ozellik['durum']; ?>" 
                                data-sira="<?php echo $ozellik['sira']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="#" class="btn btn-sm btn-danger silBtn" data-id="<?php echo $ozellik['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Özellik Ekleme Modal -->
<div class="modal fade" id="ekleModal" tabindex="-1" role="dialog" aria-labelledby="ekleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="ekleModalLabel">Yeni Özellik Ekle</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="adi">Özellik Adı</label>
                        <input type="text" class="form-control" id="adi" name="adi" value="<?php echo isset($adi) ? $adi : ''; ?>" required>
                    </div>
                    
                    <div class="form-group