<?php
// Oturumu başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proje kökünden gerekli dosyaları dahil et
require_once __DIR__ . '/config/db_baglanti.php';
require_once __DIR__ . '/models/Kullanici.php';

$kullanici_model = new Kullanici($pdo);

// Doktorları, aktif hizmetleri çek
$doktorlar = $kullanici_model->doktorlariGetir();
$aktif_hizmetler = $kullanici_model->aktifHizmetleriGetir();

// Kullanıcının giriş yapıp yapmadığını ve rolünü kontrol et
$is_logged_in = isset($_SESSION['kullanici']);
$kullanici_ad = $is_logged_in ? $_SESSION['kullanici']['ad'] : ''; 

$target_url = 'login.php';
$alert_message = '';
$is_doctor = false;
$dashboard_target_url = '#'; // Varsayılan hedef

if ($is_logged_in) {
    $rol_adi = $_SESSION['kullanici']['rol_adi']; 
    
    if ($rol_adi === 'Hasta') {
        $target_url = 'views/hasta/randevu_al.php';
        $dashboard_target_url = 'views/hasta/dashboard.php';
    } elseif ($rol_adi === 'Doktor') {
        $is_doctor = true;
        $target_url = '#';
        $alert_message = "alert('UYARI: Doktorlar bu sistem üzerinden randevu alamazlar. Lütfen kontrol panelinize dönün.');";
        $dashboard_target_url = 'views/doktor/dashboard.php';
    } else {
        // ADMIN veya diğer roller
        $dashboard_target_url = 'views/admin/dashboard.php';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoş Geldiniz - Modern Klinik Adı</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/homepage_style.css">
</head>
<body>

    <header>
        <div class="container navbar">
            <a href="homepage.php" class="logo">YirmiBeş Klinik</a>
            
            <div class="menu-toggle" id="menuToggle">☰</div> 
            
            <div class="nav-wrapper" id="navWrapper">

                <nav class="nav-links" id="navLinks">
                    <a href="#about">Hakkımızda</a>
                    <a href="#services">Hizmetlerimiz</a>
                    <a href="#doctors">Doktorlarımız</a>
                    <a href="#contact">İletişim</a>
                </nav>

                <?php if($is_logged_in){ ?>
                    <p style="color: var(--text-color);">
                        Hoş Geldiniz <span style="font-weight: 600;"><?php echo htmlspecialchars($kullanici_ad); ?></span> (<?php echo htmlspecialchars($_SESSION['kullanici']['rol_adi']); ?>)
                    </p>
                    <div class="auth-buttons">
                        <a href="<?php echo $dashboard_target_url ?>">İşlem Paneli</a>
                        <a href="logout.php">Çıkış Yap</a>
                    </div>
                <?php } else { ?>
                <div class="auth-buttons">
                    <a href="login.php">Giriş Yap</a>
                    <a href="hasta_kayit_ol.php">Kayıt Ol</a>
                </div>
                <?php }?>
            
            </div> </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Sağlığınız İçin Modern ve Güvenilir Çözümler</h1>
            <p>Uzman ekibimizle, size en kaliteli sağlık hizmetini sunmak için buradayız. Randevu alın ve sağlığınızı ertelemeyin.</p>
            
            <a href="<?php echo $target_url; ?>" class="btn" 
               <?php if ($is_doctor): ?>
                   onclick="<?php echo $alert_message; ?> return false;"
               <?php endif; ?>>
                Randevu Alın
            </a>
            
        </div>
    </section>

    <section id="about" class="about-us container">
        <div class="about-content">
            <h2 class="section-title" style="text-align: center; margin-bottom: 20px;">Hakkımızda</h2>
            <p>Kliniğimiz, yılların deneyimine sahip uzman doktor kadrosu ve son teknoloji ekipmanlarıyla, hastalarına kapsamlı ve kişiselleştirilmiş sağlık hizmeti sunmaktadır.</p>
            <p>Amacımız, her hastamızın kendini güvende ve özel hissettiği bir ortamda, en güncel tedavi yöntemleriyle iyileşmelerini sağlamaktır. Yenilikçi yaklaşımlarımız ve etik değerlere bağlılığımızla, sağlık sektöründe öncü bir rol üstleniyoruz.</p>
            <p>Hasta memnuniyetini her zaman en ön planda tutarak, sizlere sağlıklı bir yaşam sunmak için çalışıyoruz.</p>
        </div>
    </section>

    <section id="services" class="services container">
        <h2 class="section-title">Hizmetlerimiz</h2>
        <div class="service-grid">
            
            <?php if (!empty($aktif_hizmetler)): ?>
                <?php foreach ($aktif_hizmetler as $hizmet): ?>
                    <div class="service-item">
                        <h3><?php echo htmlspecialchars($hizmet); ?></h3>
                        <p><?php echo htmlspecialchars($hizmet); ?> alanında uzman doktorlarımız mevcuttur. Hemen randevu alabilirsiniz.</p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                 <p style="text-align: center; grid-column: 1 / -1;">Aktif uzmanlık alanlarına sahip doktor bulunmamaktadır. Lütfen yöneticiye başvurun.</p>
            <?php endif; ?>

        </div>
    </section>

    <section id="doctors" class="doctors container">
        <h2 class="section-title">Uzman Doktorlarımız</h2>
        <div class="doctor-grid">
            
            <?php if (!empty($doktorlar)): ?>
                <?php foreach ($doktorlar as $doktor): ?>
                    <div class="doctor-card">
                        <img src="https://via.placeholder.com/300x250?text=Dr.+<?php echo urlencode($doktor['ad']); ?>" alt="Dr. <?php echo htmlspecialchars($doktor['ad'] . ' ' . $doktor['soyad']); ?>">
                        <h3>Dr. <?php echo htmlspecialchars($doktor['ad'] . ' ' . $doktor['soyad']); ?></h3>
                        
                        <p><?php 
                            if (!empty($doktor['uzmanlik_alani'])) {
                                echo htmlspecialchars($doktor['uzmanlik_alani']);
                            } else {
                                echo htmlspecialchars($doktor['rol_adi'] ?? 'Doktor');
                            }
                        ?></p> 
                        
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; grid-column: 1 / -1;">Sistemde kayıtlı doktor bulunmamaktadır.</p>
            <?php endif; ?>

        </div>
    </section>

    <section id="contact" class="contact container">
        <h2 class="section-title">Bize Ulaşın</h2>
        <p>Sağlıkla ilgili sorularınız veya randevu talepleriniz için bize ulaşmaktan çekinmeyin.</p>
        <p>Telefon: +90 (123) 456 7890 | E-posta: info@yirmibesklinik.com</p>
        <a href="mailto:info@klinikadi.com" class="btn">E-posta Gönder</a>
    </section>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> YirmiBeş Klinik. Tüm Hakları Saklıdır.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const navWrapper = document.getElementById('navWrapper');

            if (menuToggle && navWrapper) {
                menuToggle.addEventListener('click', function() {
                    // active sınıfını ekle/kaldır
                    navWrapper.classList.toggle('active'); 
                });
            }
            
            // Ekran boyutu 768px'den büyük olduğunda menüyü kapat
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    navWrapper.classList.remove('active');
                }
            });
            
            // Eğer mobil menüden linke tıklanırsa, menüyü kapat (Kullanıcı deneyimi için)
            const navLinks = document.getElementById('navLinks');
            navLinks.addEventListener('click', function(e) {
                if (e.target.tagName === 'A' && window.innerWidth <= 768) {
                     navWrapper.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>