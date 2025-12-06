<?php
// Yetki kontrolü: Sadece 'Admin' rolü erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Admin'); 

// Bağlantı nesnesini (pdo) ve Kullanici modelini dahil et
require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Kullanici.php';

$kullanici_model = new Kullanici($pdo);
$mesaj = '';
$hata = '';

// Form gönderilmişse (Doktor Ekle butonu tıklandıysa)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = trim($_POST['ad']);
    $soyad = trim($_POST['soyad']);
    $e_posta = trim($_POST['e_posta']);
    $sifre = $_POST['sifre'];
    
    // Güvenlik: Basit alan kontrolü
    if (empty($ad) || empty($soyad) || empty($e_posta) || empty($sifre)) {
        $hata = "Lütfen tüm alanları doldurun.";
    } else {
        // Doktorlar için rol_id 2'dir (Daha önce Roller tablosuna eklemiştik).
        $rol_id = 2; 

        if ($kullanici_model->kayitOl($ad, $soyad, $e_posta, $sifre, $rol_id)) {
            $mesaj = "Dr. {$ad} {$soyad} sisteme başarıyla eklendi. İlk şifresi: {$sifre}";
            // Formu temizlemek için POST verilerini temizleyebiliriz
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
    <title>Doktor Ekle</title>
<link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Doktor Ekle</h1>
        <p><a href="dashboard.php">← Yönetici Paneline Geri Dön</a></p>

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

            <label for="sifre">Geçici Şifre:</label>
            <input type="password" id="sifre" name="sifre" required minlength="6"><br><br>

            <button type="submit">Doktoru Kaydet</button>
        </form>
    </div>
</body>
</html>