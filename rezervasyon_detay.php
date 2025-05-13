<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Rezervasyon Detayı</h1>
    <div>
        <a href="rezervasyonlar.php" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left fa-sm"></i> Geri Dön</a>
        <a href="rezervasyon_duzenle.php?id=<?php echo $rezervasyon_id; ?>" class="btn btn-sm btn-warning shadow-sm"><i class="fas fa-edit fa-sm"></i> Düzenle</a>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-primary dropdown-toggle shadow-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-cog fa-sm"></i> İşlemler
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#durumModal"><i class="fas fa-exchange-alt fa-sm fa-fw mr-2 text-gray-400"></i> Durum Değiştir</a>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#emailModal"><i class="fas fa-envelope fa-sm fa-fw mr-2 text-gray-400"></i> E-posta Gönder</a>
                <a class="dropdown-item" href="invoice.php?id=<?php echo $rezervasyon_id; ?>" target="_blank"><i class="fas fa-file-invoice fa-sm fa-fw mr-2 text-gray-400"></i> Fatura Oluştur</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#silModal"><i class="fas fa-trash fa-sm fa-fw mr-2 text-danger"></i> Rezervasyonu Sil</a>
            </div>
        </div>
    </div>
</div>

<?php if (isset($basari)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Rezervasyon durumu başarıyla güncellendi.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Rezervasyon Bilgileri -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Rezervasyon Bilgileri</h6>
                <span class="badge badge-<?php 
                    if ($rezervasyon['odeme_durumu'] == 'onaylandi') {
                        echo 'success';
                    } elseif ($rezervasyon['odeme_durumu'] == 'bekliyor') {
                        echo 'warning';
                    } else {
                        echo 'danger';
                    }
                ?> badge-lg"><?php 
                    if ($rezervasyon['odeme_durumu'] == 'onaylandi') {
                        echo 'Onaylandı';
                    } elseif ($rezervasyon['odeme_durumu'] == 'bekliyor') {
                        echo 'Bekliyor';
                    } else {
                        echo 'İptal Edildi';
                    }
                ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Rezervasyon No</th>
                                <td><?php echo $rezervasyon['rezervasyon_no']; ?></td>
                            </tr>
                            <tr>
                                <th>Oluşturma Tarihi</th>
                                <td><?php echo date('d.m.Y H:i', strtotime($rezervasyon['olusturma_tarihi'])); ?></td>
                            </tr>
                            <tr>
                                <th>Giriş Tarihi</th>
                                <td><?php echo tarihFormat($rezervasyon['giris_tarihi']); ?></td>
                            </tr>
                            <tr>
                                <th>Çıkış Tarihi</th>
                                <td><?php echo tarihFormat($rezervasyon['cikis_tarihi']); ?></td>
                            </tr>
                            <tr>
                                <th>Misafir Sayısı</th>
                                <td>
                                    <?php echo $rezervasyon['yetiskin']; ?> Yetişkin
                                    <?php if ($rezervasyon['cocuk'] > 0): ?>
                                        , <?php echo $rezervasyon['cocuk']; ?> Çocuk (0-6 yaş)
                                    <?php endif; ?>
                                    <?php if ($rezervasyon['buyuk_cocuk'] > 0): ?>
                                        , <?php echo $rezervasyon['buyuk_cocuk']; ?> Büyük Çocuk (7-12 yaş)
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Toplam Fiyat</th>
                                <td><strong><?php echo fiyatFormat($rezervasyon['toplam_fiyat']); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Ödeme Yöntemi</th>
                                <td>
                                    <?php 
                                    switch ($rezervasyon['odeme_yontemi']) {
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
                                            echo $rezervasyon['odeme_yontemi'];
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Ödeme Durumu</th>
                                <td>
                                    <?php 
                                    switch ($rezervasyon['odeme_durumu']) {
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
                                            echo $rezervasyon['odeme_durumu'];
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>IP Adresi</th>
                                <td><?php echo $rezervasyon['ip_adresi']; ?></td>
                            </tr>
                            <tr>
                                <th>Son Güncelleme</th>
                                <td><?php echo isset($rezervasyon['guncelleme_tarihi']) ? date('d.m.Y H:i', strtotime($rezervasyon['guncelleme_tarihi'])) : '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if (!empty($rezervasyon['ekstra_notlar'])): ?>
                <div class="alert alert-info mt-3">
                    <h6 class="font-weight-bold">Notlar</h6>
                    <p class="mb-0"><?php echo nl2br($rezervasyon['ekstra_notlar']); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($rezervasyon['odeme_durumu'] == 'iptal_edildi' && !empty($rezervasyon['iptal_nedeni'])): ?>
                <div class="alert alert-danger mt-3">
                    <h6 class="font-weight-bold">İptal Nedeni</h6>
                    <p class="mb-0"><?php echo nl2br($rezervasyon['iptal_nedeni']); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Oda Bilgileri -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Oda Bilgileri</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="../images/odalar/<?php echo $rezervasyon['resim']; ?>" alt="<?php echo $rezervasyon['oda_adi']; ?>" class="img-fluid rounded mb-3" style="max-height: 150px;">
                        <h5><?php echo $rezervasyon['oda_adi']; ?></h5>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Kapasite</th>
                                <td><?php echo $rezervasyon['kapasite']; ?> kişi (standart), <?php echo $rezervasyon['max_kapasite']; ?> kişi (maksimum)</td>
                            </tr>
                            <tr>
                                <th>Büyüklük</th>
                                <td><?php echo $rezervasyon['metrekare']; ?> m²</td>
                            </tr>
                            <tr>
                                <th>Özellikler</th>
                                <td>
                                    <?php
                                    $ozellik_sorgu = $db->prepare("
                                        SELECT o.adi, o.icon FROM ozellikler o
                                        INNER JOIN oda_ozellikler oo ON o.id = oo.ozellik_id
                                        WHERE oo.oda_id = ? AND o.durum = 1
                                        ORDER BY o.sira ASC
                                    ");
                                    $ozellik_sorgu->execute([$rezervasyon['oda_id']]);
                                    $ozellikler = $ozellik_sorgu->fetchAll();
                                    
                                    foreach ($ozellikler as $ozellik) {
                                        echo '<span class="badge badge-light mr-2 p-2"><i class="' . $ozellik['icon'] . ' mr-1"></i> ' . $ozellik['adi'] . '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>İşlemler</th>
                                <td>
                                    <a href="oda_detay.php?id=<?php echo $rezervasyon['oda_id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye fa-sm"></i> Oda Detayı</a>
                                    <a href="odalar.php" class="btn btn-sm btn-secondary"><i class="fas fa-list fa-sm"></i> Tüm Odalar</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Müşteri Bilgileri -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Müşteri Bilgileri</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($rezervasyon['musteri_adi'] . '+' . $rezervasyon['musteri_soyadi']); ?>&background=4e73df&color=fff&size=100" alt="<?php echo $rezervasyon['musteri_adi'] . ' ' . $rezervasyon['musteri_soyadi']; ?>" class="img-profile rounded-circle" style="width: 100px; height: 100px;">
                    <h5 class="mt-3"><?php echo $rezervasyon['musteri_adi'] . ' ' . $rezervasyon['musteri_soyadi']; ?></h5>
                </div>
                
                <table class="table table-borderless">
                    <tr>
                        <th><i class="fas fa-envelope mr-2"></i> E-posta</th>
                        <td><a href="mailto:<?php echo $rezervasyon['email']; ?>"><?php echo $rezervasyon['email']; ?></a></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-phone mr-2"></i> Telefon</th>
                        <td><a href="tel:<?php echo $rezervasyon['telefon']; ?>"><?php echo $rezervasyon['telefon']; ?></a></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-map-marker-alt mr-2"></i> Adres</th>
                        <td><?php echo nl2br($rezervasyon['adres']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-city mr-2"></i> Şehir</th>
                        <td><?php echo $rezervasyon['sehir']; ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-globe mr-2"></i> Ülke</th>
                        <td><?php echo $rezervasyon['ulke']; ?></td>
                    </tr>
                </table>
                
                <div class="text-center mt-3">
                    <a href="musteri_detay.php?id=<?php echo $rezervasyon['musteri_id']; ?>" class="btn btn-primary"><i class="fas fa-user fa-sm"></i> Müşteri Detayı</a>
                    
                    <div class="dropdown d-inline">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cog fa-sm"></i> İşlemler
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#emailModal"><i class="fas fa-envelope fa-sm fa-fw mr-2 text-gray-400"></i> E-posta Gönder</a>
                            <a class="dropdown-item" href="musteri_duzenle.php?id=<?php echo $rezervasyon['musteri_id']; ?>"><i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i> Müşteriyi Düzenle</a>
                            <a class="dropdown-item" href="rezervasyonlar.php?musteri_id=<?php echo $rezervasyon['musteri_id']; ?>"><i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i> Rezervasyonları Listele</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Rezervasyon Geçmişi -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">İşlem Geçmişi</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-item-marker">
                            <div class="timeline-item-marker-text"><?php echo date('d/m/Y', strtotime($rezervasyon['olusturma_tarihi'])); ?></div>
                            <div class="timeline-item-marker-indicator bg-primary"></div>
                        </div>
                        <div class="timeline-item-content">
                            Rezervasyon oluşturuldu
                            <div class="text-muted small"><?php echo date('H:i', strtotime($rezervasyon['olusturma_tarihi'])); ?></div>
                        </div>
                    </div>
                    
                    <?php if (isset($rezervasyon['guncelleme_tarihi'])): ?>
                    <div class="timeline-item">
                        <div class="timeline-item-marker">
                            <div class="timeline-item-marker-text"><?php echo date('d/m/Y', strtotime($rezervasyon['guncelleme_tarihi'])); ?></div>
                            <div class="timeline-item-marker-indicator bg-info"></div>
                        </div>
                        <div class="timeline-item-content">
                            Rezervasyon güncellendi
                            <div class="text-muted small"><?php echo date('H:i', strtotime($rezervasyon['guncelleme_tarihi'])); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($rezervasyon['odeme_durumu'] == 'onaylandi'): ?>
                    <div class="timeline-item">
                        <div class="timeline-item-marker">
                            <div class="timeline-item-marker-text"><?php echo date('d/m/Y', strtotime($rezervasyon['guncelleme_tarihi'] ?? $rezervasyon['olusturma_tarihi'])); ?></div>
                            <div class="timeline-item-marker-indicator bg-success"></div>
                        </div>
                        <div class="timeline-item-content">
                            Ödeme onaylandı
                            <div class="text-muted small"><?php echo date('H:i', strtotime($rezervasyon['guncelleme_tarihi'] ?? $rezervasyon['olusturma_tarihi'])); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($rezervasyon['odeme_durumu'] == 'iptal_edildi'): ?>
                    <div class="timeline-item">
                        <div class="timeline-item-marker">
                            <div class="timeline-item-marker-text"><?php echo date('d/m/Y', strtotime($rezervasyon['guncelleme_tarihi'])); ?></div>
                            <div class="timeline-item-marker-indicator bg-danger"></div>
                        </div>
                        <div class="timeline-item-content">
                            Rezervasyon iptal edildi
                            <div class="text-muted small"><?php echo date('H:i', strtotime($rezervasyon['guncelleme_tarihi'])); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Durum Değiştir Modal -->
<div class="modal fade" id="durumModal" tabindex="-1" role="dialog" aria-labelledby="durumModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="durumModalLabel">Rezervasyon Durumu Değiştir</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="yeni_durum">Yeni Durum</label>
                        <select class="form-control" id="yeni_durum" name="yeni_durum" required>
                            <option value="bekliyor" <?php echo ($rezervasyon['odeme_durumu'] == 'bekliyor') ? 'selected' : ''; ?>>Bekliyor</option>
                            <option value="onaylandi" <?php echo ($rezervasyon['odeme_durumu'] == 'onaylandi') ? 'selected' : ''; ?>>Onaylandı</option>
                            <option value="iptal_edildi" <?php echo ($rezervasyon['odeme_durumu'] == 'iptal_edildi') ? 'selected' : ''; ?>>İptal Edildi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="aciklama">Açıklama (İsteğe bağlı)</label>
                        <textarea class="form-control" id="aciklama" name="aciklama" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="email_gonder" name="email_gonder" value="1" checked>
                            <label class="custom-control-label" for="email_gonder">Müşteriye e-posta ile bildirim gönder</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary" name="durum_degistir">Durum Değiştir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- E-posta Gönder Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="islemler/email_gonder.php">
                <input type="hidden" name="rezervasyon_id" value="<?php echo $rezervasyon_id; ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">E-posta Gönder</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="alici">Alıcı</label>
                        <input type="email" class="form-control" id="alici" name="alici" value="<?php echo $rezervasyon['email']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="konu">Konu</label>
                        <input type="text" class="form-control" id="konu" name="konu" value="Rezervasyon Bilgisi: <?php echo $rezervasyon['rezervasyon_no']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="sablon">Şablon</label>
                        <select class="form-control" id="sablon" name="sablon">
                            <option value="">Özel Mesaj</option>
                            <option value="onay">Rezervasyon Onay</option>
                            <option value="iptal">Rezervasyon İptal</option>
                            <option value="hatirlatma">Giriş Hatırlatma</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mesaj">Mesaj</label>
                        <textarea class="form-control summernote" id="mesaj" name="mesaj" rows="10"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">E-posta Gönder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Silme Onay Modalı -->
<div class="modal fade" id="silModal" tabindex="-1" role="dialog" aria-labelledby="silModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="silModalLabel">Rezervasyon Silme</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Bu rezervasyonu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                <a class="btn btn-danger" href="islemler/rezervasyon_sil.php?id=<?php echo $rezervasyon_id; ?>">Sil</a>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 1rem;
    border-left: 1px solid #dee2e6;
}
.timeline-item {
    position: relative;
    padding-left: 1rem;
    padding-bottom: 1rem;
}
.timeline-item-marker {
    position: absolute;
    left: -1rem;
    width: 1rem;
}
.timeline-item-marker-text {
    position: absolute;
    left: -8rem;
    width: 6rem;
    text-align: right;
    font-size: 0.75rem;
    font-weight: 500;
}
.timeline-item-marker-indicator {
    display: block;
    width: 10px;
    height: 10px;
    border-radius: 100%;
    margin-top: 0.25rem;
    margin-left: -0.25rem;
}
.timeline-item-content {
    padding-top: 0;
    padding-bottom: 2rem;
}
</style>

<script>
// Şablon değişikliğinde içeriği doldur
$(document).ready(function() {
    $('#sablon').change(function() {
        var sablon = $(this).val();
        var summernote = $('#mesaj').summernote();
        
        if (sablon === 'onay') {
            var icerik = '<p>Sayın ' + '<?php echo $rezervasyon['musteri_adi'] . ' ' . $rezervasyon['musteri_soyadi']; ?>' + ',</p>';
            icerik += '<p><strong><?php echo $rezervasyon['rezervasyon_no']; ?></strong> numaralı rezervasyonunuz onaylanmıştır.</p>';
            icerik += '<p><strong>Giriş Tarihi:</strong> <?php echo tarihFormat($rezervasyon['giris_tarihi']); ?><br>';
            icerik += '<strong>Çıkış Tarihi:</strong> <?php echo tarihFormat($rezervasyon['cikis_tarihi']); ?><br>';
            icerik += '<strong>Oda:</strong> <?php echo $rezervasyon['oda_adi']; ?><br>';
            icerik += '<strong>Toplam Tutar:</strong> <?php echo fiyatFormat($rezervasyon['toplam_fiyat']); ?></p>';
            icerik += '<p>Rezervasyon detaylarınızı görmek için web sitemizi ziyaret edebilirsiniz.</p>';
            icerik += '<p>Bizi tercih ettiğiniz için teşekkür ederiz.</p>';
            icerik += '<p>Saygılarımızla,<br><?php echo SITE_NAME; ?> Ekibi</p>';
            
            summernote.summernote('code', icerik);
        } else if (sablon === 'iptal') {
            var icerik = '<p>Sayın ' + '<?php echo $rezervasyon['musteri_adi'] . ' ' . $rezervasyon['musteri_soyadi']; ?>' + ',</p>';
            icerik += '<p><strong><?php echo $rezervasyon['rezervasyon_no']; ?></strong> numaralı rezervasyonunuz iptal edilmiştir.</p>';
            icerik += '<p>İptal işlemi <?php echo date('d.m.Y H:i'); ?> tarihinde gerçekleştirilmiştir.</p>';
            icerik += '<p>Sorularınız veya talepleriniz için bizimle iletişime geçebilirsiniz.</p>';
            icerik += '<p>Saygılarımızla,<br><?php echo SITE_NAME; ?> Ekibi</p>';
            
            summernote.summernote('code', icerik);
        } else if (sablon === 'hatirlatma') {
            var icerik = '<p>Sayın ' + '<?php echo $rezervasyon['musteri_adi'] . ' ' . $rezervasyon['musteri_soyadi']; ?>' + ',</p>';
            icerik += '<p><strong><?php echo $rezervasyon['rezervasyon_no']; ?></strong> numaralı rezervasyonunuz için giriş tarihiniz yaklaşıyor.</p>';
            icerik += '<p><strong>Giriş Tarihi:</strong> <?php echo tarihFormat($rezervasyon['giris_tarihi']); ?><br>';
            icerik += '<strong>Çıkış Tarihi:</strong> <?php echo tarihFormat($rezervasyon['cikis_tarihi']); ?><br>';
            icerik += '<strong>Oda:</strong> <?php echo $rezervasyon['oda_adi']; ?></p>';
            icerik += '<p>Giriş işlemleri için lütfen kimlik belgelerinizi yanınızda bulundurunuz ve check-in saatimizin 14:00 olduğunu unutmayınız.</p>';
            icerik += '<p>Herhangi bir sorunuz olursa bizimle iletişime geçebilirsiniz.</p>';
            icerik += '<p>Bizi tercih ettiğiniz için teşekkür ederiz.</p>';
            icerik += '<p>Saygılarımızla,<br><?php echo SITE_NAME; ?> Ekibi</p>';
            
            summernote.summernote('code', icerik);
        } else {
            summernote.summernote('code', '');
        }
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

// Rezervasyon ID kontrol
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: rezervasyonlar.php");
    exit;
}

$rezervasyon_id = intval($_GET['id']);

// Rezervasyon bilgilerini al
$rezervasyon_sorgu = $db->prepare("
    SELECT r.*, o.adi as oda_adi, o.kapasite, o.max_kapasite, o.metrekare, o.resim, 
    m.adi as musteri_adi, m.soyadi as musteri_soyadi, m.email, m.telefon, m.adres, m.sehir, m.ulke
    FROM rezervasyonlar r
    INNER JOIN odalar o ON r.oda_id = o.id
    INNER JOIN musteriler m ON r.musteri_id = m.id
    WHERE r.id = ?
");

$rezervasyon_sorgu->execute([$rezervasyon_id]);

if ($rezervasyon_sorgu->rowCount() == 0) {
    header("Location: rezervasyonlar.php");
    exit;
}

$rezervasyon = $rezervasyon_sorgu->fetch();

// Sayfa başlığı
$sayfa_baslik = 'Rezervasyon Detayı: ' . $rezervasyon['rezervasyon_no'];

// Durum değiştir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['durum_degistir'])) {
    $yeni_durum = $_POST['yeni_durum'];
    $aciklama = isset($_POST['aciklama']) ? guvenliVeri($_POST['aciklama']) : '';
    
    // Durumu güncelle
    $guncelle = $db->prepare("
        UPDATE rezervasyonlar 
        SET odeme_durumu = ?, 
            ekstra_notlar = CONCAT(IFNULL(ekstra_notlar, ''), '\n" . date('d.m.Y H:i') . " - Durum değiştirildi: " . $rezervasyon['odeme_durumu'] . " -> " . $yeni_durum . " - " . $aciklama . "'),
            guncelleme_tarihi = NOW() 
        WHERE id = ?
    ");
    
    $guncelle->execute([$yeni_durum, $rezervasyon_id]);
    
    // E-posta gönder
    if ($guncelle->rowCount() > 0 && isset($_POST['email_gonder']) && $_POST['email_gonder'] == 1) {
        // İlgili e-posta gönderme fonksiyonunu çağır
        // durumDegisikligiEmailiGonder($rezervasyon, $yeni_durum, $aciklama);
    }
    
    // Rezervasyon bilgilerini yeniden al
    $rezervasyon_sorgu->execute([$rezervasyon_id]);
    $rezervasyon = $rezervasyon_sorgu->fetch();
    
    $basari = true;
}

// Header kısmını dahil et
include 'include/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Rezervasyon Detayı</h1>
    <div>
        <a href="rezervasyonlar.php" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left fa-sm"></i> Geri Dön</a>
        <a href="rezervasyon_duzenle.php?id=<?php echo $rezervasyon_id; ?>" class="btn btn-sm btn-warning shadow-sm"><i class="fas fa-edit fa-sm"></i> Düzenle</a>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-primary dropdown-toggle shadow-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-cog fa-sm"></i> İşlemler
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#durumModal"><i class="fas fa-exchange-alt fa-sm fa-fw mr-2 text-gray-400"></i> Durum Değiştir</a>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#emailModal"><i class="fas fa-envelope fa-sm fa-fw mr-2 text-gray-400"></i> E-posta Gönder</a>
                <a class="dropdown-item" href="invoice.php?id=<?php echo $rezervasyon_id; ?>" target="_blank"><i class="fas fa-file-invoice fa-sm fa-fw mr-2 text-gray-400"></i> Fatura Oluştur</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#silModal"><i class="fas fa-trash fa-sm fa-fw mr-2 text-danger"></i> Rezervasyonu Sil</a>
            </div>
        </div>
    </div>
</div>

<?php if (isset($basari)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Rezervasyon durumu başarıyla güncellendi.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Rezervasyon Bilgileri -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Rezervasyon Bilgileri</h6>
                <span class="badge badge-<?php 
                    if ($rezervasyon['odeme_durumu'] == 'onaylandi') {
                        echo 'success';
                    } elseif ($rezervasyon['odeme_durumu'] == 'bekliyor') {
                        echo 'warning';
                    } else {
                        echo 'danger';
                    }
                ?> badge-lg"><?php 
                    if ($rezervasyon['odeme_durumu'] == 'onaylandi') {
                        echo 'Onaylandı';
                    } elseif ($rezervasyon['odeme_durumu'] == 'bekliyor') {
                        echo 'Bekliyor';
                    } else {
                        echo 'İptal Edildi';
                    }
                ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Rezervasyon No</th>
                                <td><?php echo $rezervasyon['rezervasyon_no']; ?></td>
                            </tr>
                            <tr>
                                <th>Oluşturma Tarihi</th>
                                <td><?php echo date('d.m.Y H:i', strtotime($rezervasyon['olusturma_tarihi'])); ?></td>
                            </tr>
                            <tr>
                                <th>Giriş Tarihi</th>
                                <td><?php echo tarihFormat($rezervasyon['giris_tarihi']); ?></td>
                            </tr>
                            <tr>
                                <th>Çıkış Tarihi</th>
                                <td><?php echo tarihFormat($rezervasyon['cikis_tarihi']); ?></td>
                            </tr>
                            <tr>
                                <th>Misafir Sayısı</th>
                                <td>
                                    <?php echo $rezervasyon['yetiskin']; ?> Yetişkin
                                    <?php if ($rezervasyon['cocuk'] > 0): ?>
                                        , <?php echo $rezervasyon['cocuk']; ?> Çocuk (0-6 yaş)
                                    <?php endif; ?>
                                    <?php if ($rezervasyon['buyuk_cocuk'] > 0): ?>
                                        , <?php echo $rezervasyon['buyuk_cocuk']; ?> Büyük Çocuk (7-12 yaş)
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Toplam Fiyat</th>
                                <td><strong><?php echo fiyatFormat($rezervasyon['toplam_fiyat']); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Ödeme Yöntemi</th>
                                <td>
                                    <?php 
                                    switch ($rezervasyon['odeme_yontemi']) {
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
                                            echo $rezervasyon['odeme_yontemi'];
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Ödeme Durumu</th>
                                <td>
                                    <?php 
                                    switch ($rezervasyon['odeme_durumu']) {
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
                                            echo $rezervasyon['odeme_durumu'];
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>IP Adresi</th>
                                <td><?php echo $rezervasyon['ip_adresi']; ?></td>
                            </tr>
                            <tr>
                                <th>Son Güncelleme</th>
                                <td><?php echo isset($rezervasyon['guncelleme_tarihi']) ? date('d.m.Y H:i', strtotime($rezervasyon['guncelleme_tarihi'])) : '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if (!empty($rezervasyon['ekstra_notlar'])): ?>
                <div class="alert alert-info mt-3">
                    <h6 class="font-weight-bold">Notlar</h6>
                    <p class="mb-0"><?php echo nl2br($rezervasyon['ekstra_notlar']); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($rezervasyon['odeme_durumu'] == 'iptal_edildi' && !empty($rezervasyon['iptal_nedeni'])): ?>
                <div class="alert alert-danger mt-3">
                    <h6 class="font-weight-bold">İptal Nedeni</h6>
                    <p class="mb-0"><?php echo nl2br($rezervasyon['iptal_nedeni']); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Oda Bilgileri -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Oda Bilgileri</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="../images/odalar/<?php echo $rezervasyon['resim']; ?>" alt="<?php echo $rezervasyon['oda_adi']; ?>" class="img-fluid rounded mb-3" style="max-height: 150px;">
                        <h5><?php echo $rezervasyon['oda_adi']; ?></h5>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Kapasite</th>
                                <td><?php echo $rezervasyon['kapasite']; ?> kişi (standart), <?php echo $rezervasyon['max_kapasite']; ?> kişi (maksimum)</td>
                            </tr>
                            <tr>
                                <th>Büyüklük</th>
                                <td><?php echo $rezervasyon['metrekare']; ?> m²</td>
                            </tr>
                            <tr>
                                <th>Özellikler</th>
                                <td>
                                    <?php
                                    $ozellik_sorgu = $db->prepare("
                                        SELECT o.adi, o.icon FROM ozellikler o
                                        INNER JOIN oda_ozellikler oo ON o.id = oo.ozellik_id
                                        WHERE oo.oda_id = ? AND o.durum = 1
                                        ORDER BY o.sira ASC
                                    ");
                                    $ozellik_sorgu->execute([$rezervasyon['oda_id']]);
                                    $ozellikler = $ozellik_sorgu->fetchAll();
                                    
                                    foreach ($ozellikler as $ozellik) {
                                        echo '<span class="badge badge-light mr-2 p-2"><i class="' . $ozellik['icon'] . ' mr-1"></i> ' . $ozellik['adi'] . '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>İşlemler</th>
                                <td>
                                    <a href="oda_detay.php?id=<?php echo $rezervasyon['oda_id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye fa-sm"></i> Oda Detayı</a>
                                    <a href="odalar.php" class="btn btn-sm btn-secondary"><i class="fas fa-list fa-sm"></i> Tüm Odalar</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Müşteri Bilgileri -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Müşteri Bilgileri</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($rezervasyon['musteri_adi'] . '+' . $rezervasyon['musteri_soyadi']); ?>&background=4e73df&color=fff&size=100" alt="<?php echo $rezervasyon['musteri_adi'] . ' ' . $rezervasyon['musteri_soyadi']; ?>" class="img-profile rounded-circle" style="width: 100px; height: 100px;">
                    <h5 class="mt-3"><?php echo $rezervasyon['musteri_adi'] . ' ' . $rezervasyon['musteri_soyadi']; ?></h5>
                </div>
                
                <table class="table table-borderless">
                    <tr>
                        <th><i class="fas fa-envelope mr-2"></i> E-posta</th>
                        <td><a href="mailto:<?php echo $rezervasyon['email']; ?>"><?php echo $rezervasyon['email']; ?></a></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-phone mr-2"></i> Telefon</th>
                        <td><a href="tel:<?php echo $rezervasyon['telefon']; ?>"><?php echo $rezervasyon['telefon']; ?></a></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-map-marker-alt mr-2"></i> Adres</th>
                        <td><?php echo nl2br($rezervasyon['adres']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-city mr-2"></i> Şehir</th>
                        <td><?php echo $rezervasyon['sehir']; ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-globe mr-2"></i> Ülke</th>
                        <td><?php echo $rezervasyon['ulke']; ?></td>
                    </tr>
                </table>
                
                <div class="text-center mt-3">
                    <a href="musteri_detay.php?id=<?php echo $rezervasyon['musteri_id']; ?>" class="btn btn-primary"><i class="fas fa-user fa-sm"></i> Müşteri Detayı</a>
                    
                    <div class="dropdown d-inline">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cog fa-sm"></i> İşlemler
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#emailModal"><i class="fas fa-envelope fa-sm fa-fw mr-2 text-gray-400"></i> E-posta Gönder</a>
                            <a class="dropdown-item" href="musteri_duzenle.php?id=<?php echo $rezervasyon['musteri_id']; ?>"><i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i> Müşteriyi Düzenle</a>
                            <a class="dropdown-item" href="rezervasyonlar.php?musteri_id=<?php echo $rezervasyon['musteri_id']; ?>"><i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i> Rezervasyonları Listele</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Rezervasyon Geçmişi -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">İşlem Geçmişi</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-item-marker">
                            <div class="timeline-item-marker-text"><?php echo date('d/m/Y', strtotime($rezervasyon['olusturma_tarihi'])); ?></div>
                            <div class="timeline-item-marker-indicator bg-primary"></div>
                        </div>
                        <div class="timeline-item-content">
                            Rezervasyon oluşturuldu
                            <div class="text-muted small"><?php echo date('H:i', strtotime($rezervasyon['olusturma_tarihi'])); ?></div>
                        </div>
                    </div>
                    
                    <?php if (isset($rezervasyon['guncelleme_tarihi'])): ?>
                    <div class="timeline-item">
                        <div class="timeline-item-marker">
                            <div class="timeline-item-marker-text"><?php echo date('d/m/Y', strtotime($rezervasyon['guncelleme_tarihi'])); ?></div>
                            <div class="timeline-item-marker-indicator bg-info"></div>
                        </div>
                        <div class="timeline-item-content">
                            Rezervasyon güncellendi
                            <div class="text-muted small"><?php echo date('H:i', strtotime($rezervasyon['guncelleme_tarihi'])); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($rezervasyon['odeme_durumu'] == 'onaylandi'): ?>
                    <div class="timeline-item">
                        <div class="timeline-item-marker">
                            <div class="timeline-item-marker-text"><?php echo date('d/m/Y', strtotime($rezervasyon['guncelleme_tarihi'] ?? $rezervasyon['olusturma_tarihi'])); ?></div>
                            <div class="timeline-item-marker-indicator bg-success"></div>
                        </div>
                        <div class="timeline-item-content">
                            Ödeme onaylandı
                            <div class="text-muted small"><?php echo date('H:i', strtotime($rezervasyon['guncelleme_tarihi'] ?? $rezervasyon['olusturma_tarihi'])); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($rezervasyon['odeme_durumu'] == 'iptal_edildi'): ?>
                    <div class="timeline-item">
                        <div class="timeline-item-marker">
                            <div class="timeline-item-marker-text"><?php echo date('d/m/Y', strtotime($rezervasyon['guncelleme_tarihi'])); ?></div>
                            <div class="timeline-item-marker-indicator bg-danger"></div>
                        </div>
                        <div class="timeline-item-content">
                            Rezervasyon iptal edildi
                            <div class="text-muted small"><?php echo date('H:i', strtotime($rezervasyon['guncelleme_tarihi'])); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Durum Değiştir Modal -->
<div class="modal fade" id="durumModal" tabindex="-1" role="dialog" aria-labelledby="durumModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="durumModalLabel">Rezervasyon Durumu Değiştir</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="yeni_durum">Yeni Durum</label>
                        <select class="form-control" id="yeni_durum" name="yeni_durum" required>
                            <option value="bekliyor" <?php echo ($rezervasyon['odeme_durumu'] == 'bekliyor') ? 'selected' : ''; ?>>Bekliyor</option>
                            <option value="onaylandi" <?php echo ($rezervasyon['odeme_durumu'] == 'onaylandi') ? 'selected' : ''; ?>>Onaylandı</option>
                            <option value="iptal_edildi" <?php echo ($rezervasyon['odeme_durumu'] == 'iptal_edildi') ? 'selected' : ''; ?>>İptal Edildi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="aciklama">Açıklama (İsteğe bağlı)</label>
                        <textarea class="form-control" id="aciklama" name="aciklama" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="email_gonder" name="email_gonder" value="1" checked>
                            <label class="custom-control-label" for="email_gonder">Müşteriye e-posta ile bildirim gönder</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary" name="durum_degistir">Durum Değiştir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- E-posta Gönder Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="islemler/email_gonder.php">
                <input type="hidden" name="rezervasyon_id" value="<?php echo $rezervasyon_id; ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">E-posta Gönder</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="alici">Alıcı</label>
                        <input type="email" class="form-control" id="alici" name="alici" value="<?php echo $rezervasyon['email']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="konu">Konu</label>
                        <input type="text" class="form-control" id="konu" name="konu" value="Rezervasyon Bilgisi: <?php echo $rezervasyon['rezervasyon_no']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="sablon">Şablon</label>
                        <select class="form-control" id="sablon" name="sablon">
                            <option value="">Özel Mesaj</option>
                            <option value="onay">Rezervasyon Onay</option>
                            <option value="iptal">Rezervasyon İptal</option>
                            <option value="hatirlatma">Giriş Hatırlatma</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mesaj">Mesaj</label>
                        <textarea class="form-control summernote" id="mesaj" name="mesaj" rows="10"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">E-posta Gönder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Silme Onay Modalı -->
<div class="modal fade" id="silModal" tabindex="-1" role="dialog" aria-labelledby="silModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="silModalLabel">Rezervasyon Silme</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Bu rezervasyonu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">İptal</button>
                <a class="btn btn-danger" href="islemler/rezervasyon_sil.php?id=<?php echo $rezervasyon_id; ?>">Sil</a>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 1rem;
    border-left: 1px solid #dee2e6;
}
.timeline-item {
    position: relative;
    padding-left: 1rem;
    padding-bottom: 1rem;
}
.timeline-item-marker {
    position: absolute;
    left: -1rem;
    width: 1rem;
}
.timeline-item-marker-text {
    position: absolute;
    left: -8rem;
    width: 6rem;
    text-align: right;
    font-size: 0.75rem;
    font-weight: 500;
}
.timeline-item-marker-indicator {
    display: block;
    width: 10px;
    height: 10px;
    border-radius: 100%;
    margin-top: 0.25rem;
    margin-left: -0.25rem;
}
.<?php
session_start();
require_once '../include/config.php';
require_once '../include/db.php';
require_once '../include/functions.php';

// Rezervasyon ID kontrol
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: rezervasyonlar.php");
    exit;
}

$rezervasyon_id = intval($_GET['id']);

// Rezervasyon bilgilerini al
$rezervasyon_sorgu = $db->prepare("
    SELECT r.*, o.adi as oda_adi, o.kapasite, o.max_kapasite, o.metrekare, o.resim, 
    m.adi as musteri_adi, m.soyadi as musteri_soyadi, m.email, m.telefon, m.adres, m.sehir, m.ulke
    FROM rezervasyonlar r
    INNER JOIN odalar o ON r.oda_id = o.id
    INNER JOIN musteriler m ON r.musteri_id = m.id
    WHERE r.id = ?
");

$rezervasyon_sorgu->execute([$rezervasyon_id]);

if ($rezervasyon_sorgu->rowCount() == 0) {
    header("Location: rezervasyonlar.php");
    exit;
}

$rezervasyon = $rezervasyon_sorgu->fetch();

// Sayfa başlığı
$sayfa_baslik = 'Rezervasyon Detayı: ' . $rezervasyon['rezervasyon_no'];

// Durum değiştir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['durum_degistir'])) {
    $yeni_durum = $_POST['yeni_durum'];
    $aciklama = isset($_POST['aciklama']) ? guvenliVeri($_POST['aciklama']) : '';
    
    // Durumu güncelle
    $guncelle = $db->prepare("
        UPDATE rezervasyonlar 
        SET odeme_durumu = ?, 
            ekstra_notlar = CONCAT(IFNULL(ekstra_notlar, ''), '\n" . date('d.m.Y H:i') . " - Durum değiştirildi: " . $rezervasyon['odeme_durumu'] . " -> " . $yeni_durum . " - " . $aciklama . "'),
            guncelleme_tarihi = NOW() 
        WHERE id = ?
    ");
    
    $guncelle->execute([$yeni_durum, $rezervasyon_id]);
    
    // E-posta gönder
    if ($guncelle->rowCount() > 0 && isset($_POST['email_gonder']) && $_POST['email_gonder'] == 1) {
        // İlgili e-posta gönderme fonksiyonunu çağır
        // durumDegisikligiEmailiGonder($rezervasyon, $yeni_durum, $aciklama);
    }
    
    // Rezervasyon bilgilerini yeniden al
    $rezervasyon_sorgu->execute([$rezervasyon_id]);
    $rezervasyon = $rezervasyon_sorgu->fetch();
    
    $basari = true;
}

// Header kısmını dahil et
include 'include/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Rezervasyon Detayı</h1>
    <div>
        <a href="rezervasyonlar.php" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left fa-sm"></i> Geri Dön</a>
        <a href="rezervasyon_duzenle.php?id=<?php echo $rezervasyon_id; ?>" class="btn btn-sm btn-warning shadow-sm"><i class="fas fa-edit fa-sm"></i> Düzenle</a>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-primary dropdown-toggle shadow-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-cog fa-sm"></i> İşlemler
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#durumModal"><i class="fas fa-exchange-alt fa-sm fa-fw mr-2 text-gray-400"></i> Durum Değiştir</a>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#emailModal"><i class="fas fa-envelope fa-sm fa-fw mr-2 text-gray-400"></i> E-posta Gönder</a>
                <a class="dropdown-item" href="invoice.php?id=<?php echo $rezervasyon_id; ?>" target="_blank"><i class="fas fa-file-invoice fa-sm fa-fw mr-2 text-gray-400"></i> Fatura Oluştur</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#silModal"><i class="fas fa-trash fa-sm fa-fw mr-2 text-danger"></i> Rezervasyonu Sil</a>
            </div>
        </div>
    </div>
</div>

<?php if (isset($basari)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Rezervasyon durumu başarıyla güncellendi.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Rezervasyon Bilgileri -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Rezervasyon Bilgileri</h6>
                <span class="badge badge-<?php 
                    if ($rezervasyon['odeme_durumu'] == 'onaylandi') {
                        echo 'success';
                    } elseif ($rezervasyon['odeme_durumu'] == 'bekliyor') {
                        echo 'warning';
                    } else {
                        echo 'danger';
                    }
                ?> badge-lg"><?php 
                    if ($rezervasyon['odeme_durumu'] == 'onaylandi') {
                        echo 'Onaylandı';
                    } elseif ($rezervasyon['odeme_durumu'] == 'bekliyor') {
                        echo 'Bekliyor';
                    } else {
                        echo 'İptal Edildi';
                    }
                ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Rezervasyon No</th>
                                <td><?php echo $rezervasyon['rezervasyon_no']; ?></td>
                            </tr>
                            <tr>
                                <th>Oluşturma Tarihi</th>
                                <td><?php echo date('d.m.Y H:i', strtotime($rezervasyon['olusturma_tarihi'])); ?></td>
                            </tr>
                            <tr>
                                <th>Giriş Tarihi</th>
                                <td><?php echo tarihFormat($rezervasyon['giris_tarihi']); ?></td>
                            </tr>
                            <tr>
                                <th>Çıkış Tarihi</th>
                                <td><?php echo tarihFormat($rezervasyon['cikis_tarihi']); ?></td>
                            </tr>
                            <tr>
                                <th>Misafir Sayısı</th>
                                <td>