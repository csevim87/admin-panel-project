<?php
// Oturum kontrolü
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Admin bilgilerini al
$admin_id = $_SESSION['admin_id'];
$admin_adi = $_SESSION['admin_adi'];
$admin_soyadi = $_SESSION['admin_soyadi'];
$admin_yetki = $_SESSION['admin_yetki'];

// Aktif sayfayı belirle
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($sayfa_baslik) ? $sayfa_baslik : 'Admin Paneli'; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    
    <!-- Bootstrap Datepicker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
    
    <!-- Summernote -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css">
    
    <!-- Admin CSS -->
    <style>
        body {
            background-color: #f8f9fc;
        }
        
        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
            position: fixed;
            z-index: 1;
            width: 250px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-item {
            position: relative;
        }
        
        .sidebar .nav-item .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
        }
        
        .sidebar .nav-item .nav-link:hover {
            color: #fff;
        }
        
        .sidebar .nav-item .nav-link.active {
            color: #fff;
            font-weight: 700;
        }
        
        .sidebar .nav-item .nav-link i {
            margin-right: 0.5rem;
            width: 1.25rem;
            text-align: center;
        }
        
        .sidebar .sidebar-heading {
            padding: 0 1rem;
            font-weight: 800;
            font-size: 0.65rem;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            margin-top: 1rem;
        }
        
        .sidebar-brand {
            height: 4.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            font-size: 1.2rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
        }
        
        .sidebar-brand:hover {
            color: #fff;
            text-decoration: none;
        }
        
        .sidebar .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin: 0 1rem 1rem;
        }
        
        /* Content */
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 1.5rem;
            transition: all 0.3s;
        }
        
        /* Topbar */
        .topbar {
            height: 4.375rem;
            background-color: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            position: relative;
            z-index: 1;
        }
        
        .topbar .navbar-nav .nav-item .nav-link {
            color: #3a3b45;
            padding: 0 1rem;
            position: relative;
        }
        
        .topbar .navbar-nav .nav-item .nav-link .badge-counter {
            position: absolute;
            transform: scale(0.7);
            transform-origin: top right;
            right: 0.5rem;
            top: 0.25rem;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .dropdown-item.active, .dropdown-item:active {
            background-color: #4e73df;
        }
        
        /* Cards */
        .card {
            margin-bottom: 24px;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        
        /* Buttons */
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .btn-success {
            background-color: #1cc88a;
            border-color: #1cc88a;
        }
        
        .btn-success:hover {
            background-color: #17a673;
            border-color: #169b6b;
        }
        
        .btn-info {
            background-color: #36b9cc;
            border-color: #36b9cc;
        }
        
        .btn-info:hover {
            background-color: #2c9faf;
            border-color: #2a96a5;
        }
        
        .btn-warning {
            background-color: #f6c23e;
            border-color: #f6c23e;
        }
        
        .btn-warning:hover {
            background-color: #f4b619;
            border-color: #f4b30d;
        }
        
        .btn-danger {
            background-color: #e74a3b;
            border-color: #e74a3b;
        }
        
        .btn-danger:hover {
            background-color: #e02d1b;
            border-color: #d52a1a;
        }
        
        /* Mobile */
        @media (max-width: 768px) {
            .sidebar {
                width: 100px;
            }
            
            .sidebar .nav-item .nav-link span {
                display: none;
            }
            
            .sidebar .nav-item .nav-link {
                text-align: center;
                padding: 0.75rem 1rem;
            }
            
            .sidebar .nav-item .nav-link i {
                margin-right: 0;
                font-size: 1.25rem;
            }
            
            .sidebar .sidebar-heading {
                text-align: center;
                padding: 0;
            }
            
            .sidebar-brand {
                padding: 1.5rem 0;
            }
            
            .sidebar-brand-text {
                display: none;
            }
            
            .content {
                margin-left: 100px;
            }
        }
        
        /* Toggle Sidebar */
        .sidebar-toggled .sidebar {
            width: 100px;
        }
        
        .sidebar-toggled .sidebar .nav-item .nav-link span {
            display: none;
        }
        
        .sidebar-toggled .sidebar .nav-item .nav-link {
            text-align: center;
            padding: 0.75rem 1rem;
        }
        
        .sidebar-toggled .sidebar .nav-item .nav-link i {
            margin-right: 0;
            font-size: 1.25rem;
        }
        
        .sidebar-toggled .sidebar .sidebar-heading {
            text-align: center;
            padding: 0;
        }
        
        .sidebar-toggled .sidebar-brand {
            padding: 1.5rem 0;
        }
        
        .sidebar-toggled .sidebar-brand-text {
            display: none;
        }
        
        .sidebar-toggled .content {
            margin-left: 100px;
        }
    </style>
</head>
<body>
    <!-- Page Wrapper -->
    <div class="wrapper d-flex">
        <!-- Sidebar -->
        <nav class="sidebar">
            <a class="sidebar-brand" href="dashboard.php">
                <i class="fas fa-hotel mr-2"></i>
                <span class="sidebar-brand-text"><?php echo SITE_NAME; ?></span>
            </a>
            
            <hr class="sidebar-divider">
            
            <div class="sidebar-heading">Ana Menü</div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'rezervasyonlar.php') ? 'active' : ''; ?>" href="rezervasyonlar.php">
                        <i class="fas fa-fw fa-calendar-check"></i>
                        <span>Rezervasyonlar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'odalar.php') ? 'active' : ''; ?>" href="odalar.php">
                        <i class="fas fa-fw fa-bed"></i>
                        <span>Odalar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'musteriler.php') ? 'active' : ''; ?>" href="musteriler.php">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Müşteriler</span>
                    </a>
                </li>
            </ul>
            
            <hr class="sidebar-divider">
            
            <div class="sidebar-heading">Sistem</div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'ozellikler.php') ? 'active' : ''; ?>" href="ozellikler.php">
                        <i class="fas fa-fw fa-list"></i>
                        <span>Özellikler</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'ekstra_hizmetler.php') ? 'active' : ''; ?>" href="ekstra_hizmetler.php">
                        <i class="fas fa-fw fa-concierge-bell"></i>
                        <span>Ekstra Hizmetler</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'odeme_ayarlari.php') ? 'active' : ''; ?>" href="odeme_ayarlari.php">
                        <i class="fas fa-fw fa-credit-card"></i>
                        <span>Ödeme Ayarları</span>
                    </a>
                </li>
                <?php if ($admin_yetki == 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'kullanicilar.php') ? 'active' : ''; ?>" href="kullanicilar.php">
                        <i class="fas fa-fw fa-user-shield"></i>
                        <span>Kullanıcılar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'ayarlar.php') ? 'active' : ''; ?>" href="ayarlar.php">
                        <i class="fas fa-fw fa-cogs"></i>
                        <span>Site Ayarları</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <hr class="sidebar-divider">
            
            <div class="text-center mt-4 d-none d-md-inline">
                <button class="btn rounded-circle border-0" id="sidebarToggle">
                    <i class="fas fa-angle-left text-white"></i>
                </button>
            </div>
        </nav>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper d-flex flex-column w-100">
            <!-- Topbar -->
            <nav class="topbar navbar navbar-expand navbar-light bg-white mb-4">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown no-arrow mx-1">
                        <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell fa-fw"></i>
                            <span class="badge badge-danger badge-counter">3+</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                            <h6 class="dropdown-header">Bildirimler</h6>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="mr-3">
                                    <div class="icon-circle bg-primary">
                                        <i class="fas fa-calendar-check text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-gray-500">12 Haziran 2023</div>
                                    <span>Yeni bir rezervasyon oluşturuldu!</span>
                                </div>
                            </a>
                            <a class="dropdown-item text-center small text-gray-500" href="#">Tüm Bildirimleri Göster</a>
                        </div>
                    </li>
                    
                    <div class="topbar-divider d-none d-sm-block"></div>
                    
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $admin_adi . ' ' . $admin_soyadi; ?></span>
                            <img class="img-profile rounded-circle" src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin_adi . '+' . $admin_soyadi); ?>&background=4e73df&color=fff" width="32" height="32">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="profil.php">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                Profil
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Çıkış Yap
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
            
            <!-- Main Content -->
            <div class="content">