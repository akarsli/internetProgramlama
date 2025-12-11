<?php

require_once __DIR__ . '/models/Kullanici.php';

$kullanici_model = new Kullanici($pdo);
$mesaj = '';
$hata = '';

// Form gönderilmişse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = trim($_POST['ad']);
    $soyad = trim($_POST['soyad']);
    $e_posta = trim($_POST['e_posta']);
    $sifre = $_POST['sifre'];
    
    if (empty($ad) || empty($soyad) || empty($e_posta) || empty($sifre)) {
        $hata = "Lütfen tüm alanları doldurun.";
    } else {
        $rol_id = 3; // Hasta rolü

        // 1. Kullanicilar tablosuna kayıt yap ve eklenen ID'yi al
        $yeni_kullanici_id = $kullanici_model->kayitOl($ad, $soyad, $e_posta, $sifre, $rol_id);

        if ($yeni_kullanici_id) {
            
            // YALNIZCA HASTA İSE: 2. Hastalar tablosuna kayıt yap
            if ($rol_id == 3) {
                if ($kullanici_model->hastaDetayEkle($yeni_kullanici_id)) {
                    $mesaj = "Hasta {$ad} {$soyad} sisteme başarıyla eklendi.";
                } else {
                    // Kullanici kaydı başarılı oldu, ancak Hastalar kaydı başarısız oldu.
                    $hata = "Kullanıcı kaydı başarılı, ancak hasta detay (Hastalar tablosu) eklenemedi. Yöneticiye başvurun.";
                    // Gelişmiş sistemlerde bu durumda Kullanici kaydının da geri alınması (rollback) gerekir.
                }
            } else {
                // Doktor veya Admin ise sadece Kullanicilar tablosu yeterli
                 $mesaj = "Kullanıcı {$ad} {$soyad} sisteme başarıyla eklendi.";
            }

            $_POST = array(); 
        } else {
            $hata = "Kayıt işlemi başarısız. Bu e-posta zaten kullanımda olabilir.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Ekle</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Hasta Kayıt Ekranı</h1>
        <p><a href="homepage.php">← Ana Sayfaya Geri Dön</a></p>

        <?php if ($mesaj): ?>
            <p style="color: green; font-weight: bold;"><?php echo $mesaj; ?></p>
        <?php endif; ?>
        <?php if ($hata): ?>
            <p style="color: red; font-weight: bold;"><?php echo $hata; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="ad">Ad:</label>
            <input type="text" id="ad" name="ad" required><br><br>

            <label for="soyad">Soyad:</label>
            <input type="text" id="soyad" name="soyad" required><br><br>
            
            <label for="e_posta">E-posta (Kullanıcı Adı):</label>
            <input type="email" id="e_posta" name="e_posta" required><br><br>

            <label for="sifre">Şifre:</label>
            <input type="password" id="sifre" name="sifre" required minlength="6"><br><br>

            <button type="submit">Hastayı Kaydet</button>
        </form>
    </div>
</body>
</html>