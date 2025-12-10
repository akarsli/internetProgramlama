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
    <style>
        /* Genel Stil Ayarları */
        :root {
            --primary-color: #2c7efc; 
            --secondary-color: #f7f9fc; 
            --text-color: #333;
            --light-text-color: #666;
            --white-color: #fff;
            --border-color: #eee;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Başlık (Header) */
        header {
            background-color: var(--white-color);
            padding: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo {
            font-family: 'Montserrat', sans-serif;
            font-size: 2em;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        /* Navigasyon Linklerini Saran Yapı */
        .nav-links {
            transition: all 0.3s ease-in-out;
            display: flex; 
            align-items: center;
            gap: 25px;
        }

        .nav-links a {
            font-family: 'Montserrat', sans-serif;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }
        
        /* Auth Butonları Stili */
        .auth-buttons {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .auth-buttons a {
            background-color: var(--primary-color);
            color: var(--white-color);
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .auth-buttons a:hover {
            background-color: #226bdf;
        }
        
        /* PHP ile gelen Hoş Geldiniz Mesajı */
        .navbar p {
            margin: 0;
            white-space: nowrap;
        }

        /* HAMBURGER İKONU VE MOBİL MENÜ STİLLERİ */
        .menu-toggle {
            display: none; 
            cursor: pointer;
            font-size: 30px;
            line-height: 0;
            color: var(--primary-color);
        }

        /* Mobil menü içeriğini saran yapı */
        .nav-wrapper {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        /* Kahraman Bölümü (Hero Section) */
        .hero {
            background: linear-gradient(rgba(44, 126, 252, 0.8), rgba(44, 126, 252, 0.8)), url('https://via.placeholder.com/1600x600?text=Modern+Klinik+Görseli') no-repeat center center/cover;
            color: var(--white-color);
            text-align: center;
            padding: 100px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
        }

        .hero h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 3.5em;
            margin-bottom: 20px;
            font-weight: 700;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.3em;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .hero .btn {
            background-color: var(--white-color);
            color: var(--primary-color);
            padding: 15px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1em;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .hero .btn:hover {
            background-color: var(--text-color);
            color: var(--white-color);
        }

        /* Bölüm Başlıkları */
        .section-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.5em;
            text-align: center;
            margin-bottom: 60px;
            color: var(--primary-color);
            font-weight: 700;
            position: relative;
        }
        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background-color: var(--primary-color);
            margin: 15px auto 0;
            border-radius: 2px;
        }

        /* Hakkımızda Bölümü */
        .about-us {
            padding: 80px 0;
            background-color: var(--white-color);
            display: flex;
            align-items: center;
            gap: 40px;
            flex-wrap: wrap;
        }

        .about-content {
            flex: 1;
            min-width: 300px;
        }
        .about-content h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2em;
            color: var(--text-color);
            margin-bottom: 20px;
        }
        .about-content p {
            color: var(--light-text-color);
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .about-image {
            flex: 1;
            min-width: 300px;
            text-align: center;
        }
        .about-image img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* Hizmetler Bölümü */
        .services {
            padding: 80px 0;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .service-item {
            background-color: var(--white-color);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .service-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .service-item img {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }

        .service-item h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.5em;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .service-item p {
            color: var(--light-text-color);
            font-size: 1em;
        }

        /* Doktorlarımız Bölümü */
        .doctors {
            padding: 80px 0;
            background-color: var(--secondary-color);
        }

        .doctor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .doctor-card {
            background-color: var(--white-color);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            text-align: center;
            padding-bottom: 20px;
        }

        .doctor-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-bottom: 3px solid var(--primary-color);
        }

        .doctor-card h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.4em;
            color: var(--text-color);
            margin: 20px 0 5px;
        }

        .doctor-card p {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 15px;
        }

        /* İletişim Bölümü */
        .contact {
            padding: 80px 0;
            background-color: var(--white-color);
            text-align: center;
        }
        .contact p {
            font-size: 1.2em;
            color: var(--light-text-color);
            margin-bottom: 30px;
        }
        .contact .btn {
            background-color: var(--primary-color);
            color: var(--white-color);
            padding: 15px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }
        .contact .btn:hover {
            background-color: #226bdf;
        }

        /* Alt Bilgi (Footer) */
        footer {
            background-color: var(--text-color);
            color: var(--white-color);
            text-align: center;
            padding: 30px 20px;
            font-size: 0.9em;
        }
        footer a {
            color: var(--primary-color);
            text-decoration: none;
        }

        /* Duyarlı Tasarım (Mobil Görünüm) */
        @media (max-width: 768px) {
            .navbar {
                justify-content: space-between;
                padding: 10px 20px;
            }
            
            .menu-toggle {
                display: block; /* Hamburger ikonunu göster */
            }

            /* Tüm menü içeriğini (linkler, mesaj, butonlar) saran yapıyı gizle */
            .nav-wrapper {
                display: none; 
                flex-direction: column; 
                width: 100%; 
                text-align: center;
                margin-top: 15px;
                padding-top: 15px;
                background-color: var(--white-color);
                border-top: 1px solid var(--border-color);
            }
            
            /* Mobil menü aktifken içeriği göster */
            .nav-wrapper.active {
                display: flex;
            }

            /* Nav Linkleri, Mesaj ve Butonları dikey yap */
            .nav-links, .auth-buttons, .navbar p {
                flex-direction: column; 
                width: 100%;
                margin: 0;
            }

            .nav-links a, .auth-buttons a {
                margin: 8px 0;
                padding: 12px;
                border-bottom: 1px solid var(--secondary-color);
            }
            
            /* Mesajı mobil menüde ortala ve sıraya koy */
            .navbar p {
                padding: 10px 0;
                border-bottom: 1px solid var(--secondary-color);
                order: -1; /* En üste taşır */
            }
            
            /* Diğer mobil ayarlamalar */
            .hero h1 {
                font-size: 2.5em;
            }
            .about-us {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="container navbar">
            <a href="homepage.php" class="logo">Klinik Adı</a>
            
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
                    <a href="register.php">Kayıt Ol</a>
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
            <h2 class="section-title" style="text-align: left; margin-bottom: 20px;">Hakkımızda</h2>
            <p>Kliniğimiz, yılların deneyimine sahip uzman doktor kadrosu ve son teknoloji ekipmanlarıyla, hastalarına kapsamlı ve kişiselleştirilmiş sağlık hizmeti sunmaktadır.</p>
            <p>Amacımız, her hastamızın kendini güvende ve özel hissettiği bir ortamda, en güncel tedavi yöntemleriyle iyileşmelerini sağlamaktır. Yenilikçi yaklaşımlarımız ve etik değerlere bağlılığımızla, sağlık sektöründe öncü bir rol üstleniyoruz.</p>
            <p>Hasta memnuniyetini her zaman en ön planda tutarak, sizlere sağlıklı bir yaşam sunmak için çalışıyoruz.</p>
        </div>
        <div class="about-image">
            <img src="https://via.placeholder.com/600x400?text=Klinik+İçi+Görsel" alt="Klinik İç Mekan">
        </div>
    </section>

    <section id="services" class="services container">
        <h2 class="section-title">Hizmetlerimiz</h2>
        <div class="service-grid">
            
            <?php if (!empty($aktif_hizmetler)): ?>
                <?php foreach ($aktif_hizmetler as $hizmet): ?>
                    <div class="service-item">
                        <img src="https://via.placeholder.com/80x80?text=<?php echo urlencode(substr($hizmet, 0, 5)); ?>" alt="<?php echo htmlspecialchars($hizmet); ?>">
                        
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
        <p>Telefon: +90 (123) 456 7890 | E-posta: info@klinikadi.com</p>
        <a href="mailto:info@klinikadi.com" class="btn">E-posta Gönder</a>
    </section>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Klinik Adı. Tüm Hakları Saklıdır.</p>
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