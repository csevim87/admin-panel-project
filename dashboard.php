<?php
session_start();
require_once '../include/config.php';
require_once '../include/db.php';
require_once '../include/functions.php';

// Sayfa başlığı
$sayfa_baslik = 'Dashboard';

// Özet bilgileri getir
// Toplam rezervasyon sayısı
$rez_toplam_sorgu = $db->query("SELECT COUNT(*) as sayi FROM rezervasyonlar");
$rez_toplam = $rez_toplam_sorgu->fetch()['sayi'];

// Aktif rezervasyon sayısı
$rez_aktif_sorgu = $db->query("SELECT COUNT(*) as sayi FROM rezervasyonlar WHERE odeme_durumu != 'iptal_edildi'");
$rez_aktif = $rez_aktif_sorgu->fetch()['sayi'];

// Bekleyen ödeme sayısı
$rez_bekleyen_sorgu = $db->query("SELECT COUNT(*) as sayi FROM rezervasyonlar WHERE odeme_durumu = 'bekliyor'");
$rez_bekleyen = $rez_bekleyen_sorgu->fetch()['sayi'];

// Toplam müşteri sayısı
$musteri_sorgu = $db->query("SELECT COUNT(*) as sayi FROM musteriler");
$musteri_toplam = $musteri_sorgu->fetch()['sayi'];

// Toplam oda sayısı
$oda_sorgu = $db->query("SELECT COUNT(*) as sayi FROM odalar");
$oda_toplam = $oda_sorgu->fetch()['sayi'];

// Toplam gelir
$gelir_sorgu = $db->query("SELECT SUM(toplam_fiyat) as toplam FROM rezervasyonlar WHERE odeme_durumu = 'onaylandi'");
$toplam_gelir = $gelir_sorgu->fetch()['toplam'];

// Son 5 rezervasyon
$son_rez_sorgu = $db->query("
    SELECT r.*, o.adi as oda_adi, m.adi as musteri_adi, m.soyadi as musteri_soyadi 
    FROM rezervasyonlar r
    INNER JOIN odalar o ON r.oda_id = o.id
    INNER JOIN musteriler m ON r.musteri_id = m.id
    ORDER BY r.olusturma_tarihi DESC
    LIMIT 5
");

// Son 5 günlük rezervasyon sayısı (grafik için)
$gun_rez_sorgu = $db->query("
    SELECT DATE(olusturma_tarihi) as tarih, COUNT(*) as sayi
    FROM rezervasyonlar
    WHERE olusturma_tarihi >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(olusturma_tarihi)
    ORDER BY tarih ASC
");

$gun_rez_data = [];
$gun_rez_labels = [];

while ($row = $gun_rez_sorgu->fetch()) {
    $gun_rez_labels[] = date('d.m.Y', strtotime($row['tarih']));
    $gun_rez_data[] = $row['sayi'];
}

// Bugün için oda doluluk oranı
$bugun = date('Y-m-d');
$dolu_oda_sorgu = $db->prepare("
    SELECT COUNT(*) as sayi
    FROM rezervasyonlar
    WHERE odeme_durumu != 'iptal_edildi'
    AND giris_tarihi <= ?
    AND cikis_tarihi > ?
");

$dolu_oda_sorgu->execute([$bugun, $bugun]);
$dolu_oda = $dolu_oda_sorgu->fetch()['sayi'];

$doluluk_orani = 0;
if ($oda_toplam > 0) {
    $doluluk_orani = round(($dolu_oda / $oda_toplam) * 100);
}

// Header kısmını dahil et
include 'include/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Rapor Oluştur</a>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Toplam Rezervasyon -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Toplam Rezervasyon</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $rez_toplam; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aktif Rezervasyon -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aktif Rezervasyon</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $rez_aktif; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bekleyen Ödeme -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Bekleyen Ödeme</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $rez_bekleyen; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toplam Gelir -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Toplam Gelir</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo fiyatFormat($toplam_gelir); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Rezervasyon Grafik -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Son 7 Gün Rezervasyon İstatistikleri</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="myAreaChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Doluluk Oranı -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Bugün Oda Doluluk Oranı</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="myPieChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-primary"></i> Dolu (<?php echo $dolu_oda; ?>)
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Boş (<?php echo $oda_toplam - $dolu_oda; ?>)
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Son Rezervasyonlar -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Son Rezervasyonlar</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Rezervasyon No</th>
                                <th>Müşteri</th>
                                <th>Oda</th>
                                <th>Tarih</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($rez = $son_rez_sorgu->fetch()): ?>
                            <tr>
                                <td><a href="rezervasyon_detay.php?id=<?php echo $rez['id']; ?>"><?php echo $rez['rezervasyon_no']; ?></a></td>
                                <td><?php echo $rez['musteri_adi'] . ' ' . $rez['musteri_soyadi']; ?></td>
                                <td><?php echo $rez['oda_adi']; ?></td>
                                <td><?php echo tarihFormat($rez['giris_tarihi']); ?> - <?php echo tarihFormat($rez['cikis_tarihi']); ?></td>
                                <td>
                                    <?php if ($rez['odeme_durumu'] == 'onaylandi'): ?>
                                        <span class="badge badge-success">Onaylandı</span>
                                    <?php elseif ($rez['odeme_durumu'] == 'bekliyor'): ?>
                                        <span class="badge badge-warning">Bekliyor</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">İptal</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-center">
                    <a href="rezervasyonlar.php" class="btn btn-sm btn-primary">Tüm Rezervasyonlar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Genel Bilgiler -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Genel Bilgiler</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card bg-primary text-white shadow">
                            <div class="card-body">
                                <div class="text-white-50 small">Toplam Oda</div>
                                <div class="text-lg font-weight-bold"><?php echo $oda_toplam; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card bg-success text-white shadow">
                            <div class="card-body">
                                <div class="text-white-50 small">Toplam Müşteri</div>
                                <div class="text-lg font-weight-bold"><?php echo $musteri_toplam; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="card bg-info text-white shadow">
                        <div class="card-body">
                            <div class="text-white-50 small">Bugünkü Giriş Yapacak Misafirler</div>
                            <?php
                            $bugun_giris_sorgu = $db->prepare("
                                SELECT COUNT(*) as sayi 
                                FROM rezervasyonlar 
                                WHERE giris_tarihi = ? 
                                AND odeme_durumu != 'iptal_edildi'
                            ");
                            $bugun_giris_sorgu->execute([$bugun]);
                            $bugun_giris = $bugun_giris_sorgu->fetch()['sayi'];
                            ?>
                            <div class="text-lg font-weight-bold"><?php echo $bugun_giris; ?> Misafir</div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="card bg-warning text-white shadow">
                        <div class="card-body">
                            <div class="text-white-50 small">Bugünkü Çıkış Yapacak Misafirler</div>
                            <?php
                            $bugun_cikis_sorgu = $db->prepare("
                                SELECT COUNT(*) as sayi 
                                FROM rezervasyonlar 
                                WHERE cikis_tarihi = ? 
                                AND odeme_durumu != 'iptal_edildi'
                            ");
                            $bugun_cikis_sorgu->execute([$bugun]);
                            $bugun_cikis = $bugun_cikis_sorgu->fetch()['sayi'];
                            ?>
                            <div class="text-lg font-weight-bold"><?php echo $bugun_cikis; ?> Misafir</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <a href="#" class="btn btn-sm btn-primary">Detaylı Rapor</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grafikler için JavaScript -->
<script>
// Sayfa yüklendiğinde çalışacak script
document.addEventListener("DOMContentLoaded", function() {
    // Rezervasyon grafiği
    var ctx = document.getElementById("myAreaChart");
    var myLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($gun_rez_labels); ?>,
            datasets: [{
                label: "Rezervasyon Sayısı",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: <?php echo json_encode($gun_rez_data); ?>,
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                xAxes: [{
                    time: {
                        unit: 'date'
                    },
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                }],
                yAxes: [{
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        beginAtZero: true,
                        callback: function(value, index, values) {
                            return value;
                        }
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: {
                display: false
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel + ': ' + tooltipItem.yLabel;
                    }
                }
            }
        }
    });
    
    // Doluluk oranı pasta grafiği
    var ctx = document.getElementById("myPieChart");
    var myPieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ["Dolu", "Boş"],
            datasets: [{
                data: [<?php echo $dolu_oda; ?>, <?php echo $oda_toplam - $dolu_oda; ?>],
                backgroundColor: ['#4e73df', '#1cc88a'],
                hoverBackgroundColor: ['#2e59d9', '#17a673'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
            legend: {
                display: false
            },
            cutoutPercentage: 80,
        },
    });
});
</script>

<?php
// Footer kısmını dahil et
include 'include/footer.php';
?>