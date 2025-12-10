<?php
// Yetki kontrolü: Sadece Doktor erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Hasta'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Kullanici.php';

$kullanici_model = new Kullanici($pdo);
$hasta_id = $_SESSION['kullanici']['kullanici_id'];
$mesaj = '';
$hata = '';

// 1. Mevcut bilgileri çek (Formu önceden doldurmak için)
$hasta_bilgi = $kullanici_model->idIleKullaniciGetir($hasta_id);

if (!$hasta_bilgi) {
    // Kendi kaydını bulamama durumu olası değil, ancak güvenlik için eklenmeli.
    die("Hata: Kullanıcı bilgisi bulunamadı.");
}

// 2. Form gönderilmişse (Güncelleme işlemi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guncelle'])) {
    $ad = trim($_POST['ad']);
    $soyad = trim($_POST['soyad']);
    $e_posta = trim($_POST['e_posta']);
    $telefon = trim($_POST['telefon']);

    if (empty($ad) || empty($soyad) || empty($e_posta)) {
        $hata = "Ad, Soyad ve E-posta alanları boş bırakılamaz.";
    } else {
        if ($kullanici_model->kullaniciBilgiGuncelle($hasta_id, $ad, $soyad, $e_posta, $telefon)) {
            $mesaj = "Bilgileriniz başarıyla güncellendi!";
            // Oturum bilgilerini de güncelle (Aksi takdirde eski ad görünür)
            $_SESSION['kullanici']['ad'] = $ad;
            $_SESSION['kullanici']['soyad'] = $soyad;
            $_SESSION['kullanici']['e_posta'] = $e_posta;

            // Güncel verileri tekrar çek
            $hasta_bilgi = $kullanici_model->idIleKullaniciGetir($hasta_id);
        } else {
            $hata = "Güncelleme sırasında bir hata oluştu. E-posta adresi zaten kullanılıyor olabilir.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kişisel Bilgileri Düzenle</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css"> 
</head>
<body>
    <div class="container">
        <h1>Kişisel Bilgileri Düzenle</h1>
        <p><a href="dashboard.php">← Hasta Paneline Geri Dön</a></p>

        <?php if ($mesaj): ?>
            <p class="mesaj-basarili"><?php echo $mesaj; ?></p>
        <?php endif; ?>
        <?php if ($hata): ?>
            <p class="mesaj-hata"><?php echo $hata; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="ad">Ad:</label>
            <input type="text" id="ad" name="ad" required 
                   value="<?php echo htmlspecialchars($hasta_bilgi['ad']); ?>">

            <label for="soyad">Soyad:</label>
            <input type="text" id="soyad" name="soyad" required 
                   value="<?php echo htmlspecialchars($hasta_bilgi['soyad']); ?>">
            
            <label for="e_posta">E-posta:</label>
            <input type="email" id="e_posta" name="e_posta" required 
                   value="<?php echo htmlspecialchars($hasta_bilgi['e_posta']); ?>">

            <label for="telefon">Telefon:</label>
            <input type="text" id="telefon" name="telefon" 
                   value="<?php echo htmlspecialchars($hasta_bilgi['telefon'] ?? ''); ?>">

            <button type="submit" name="guncelle">Bilgileri Güncelle</button>
        </form>

        <p><a href="sifre_degistir.php">Şifremi Değiştir</a></p>
    </div>
</body>
</html>