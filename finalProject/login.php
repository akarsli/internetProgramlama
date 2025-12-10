<?php
// Oturum başlatma: Kullanıcının giriş durumunu takip etmek için gereklidir.
session_start();

// Veritabanı bağlantısını ve Kullanici sınıfını dahil et
require_once __DIR__ . '/config/db_baglanti.php';
require_once __DIR__ . '/models/Kullanici.php';

// Kullanıcı modelini oluştur
$kullanici_model = new Kullanici($pdo);
$hata = '';

// Eğer kullanıcı zaten giriş yapmışsa, rolüne göre yönlendir
if (isset($_SESSION['kullanici'])) {
    $rol = $_SESSION['kullanici']['rol_adi'];
    header('Location: views/' . strtolower($rol) . '/dashboard.php');
    exit;
}


// Form gönderilmişse (Giriş yap butonu tıklandıysa)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['giris'])) {
    $e_posta = trim($_POST['e_posta']);
    $sifre   = $_POST['sifre'];

    $giris_sonuc = $kullanici_model->girisYap($e_posta, $sifre);

    if ($giris_sonuc) {
        // Giriş başarılı! Oturumu başlat
        $_SESSION['kullanici'] = $giris_sonuc;
        
        // Kullanıcı rolüne göre Dashboard'a yönlendir
        $rol = strtolower($giris_sonuc['rol_adi']);
        
        // Örnek: admin rolündeki kullanıcıyı /views/admin/dashboard.php'ye yönlendir
        header("Location: homepage.php");
        exit;
    } else {
        $hata = 'E-posta veya şifre hatalı.';
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Klinik Sistemi Giriş</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container"> 
        <h1>Klinik Yönetim Sistemi Girişi</h1>
        
        <?php if ($hata): ?>
            <p class="mesaj-hata"><?php echo $hata; ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="e_posta">E-posta:</label>
            <input type="email" id="e_posta" name="e_posta" required>

            <label for="sifre">Şifre:</label>
            <input type="password" id="sifre" name="sifre" required>

            <button type="submit" name="giris">Giriş Yap</button>
            <a href="hasta_kayit_ol.php">Kayıt olmadınız mı?</a>
        </form>
    </div>
</body>
</html>