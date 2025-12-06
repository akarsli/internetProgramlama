<?php
// Yetki kontrolü: Sadece Doktor erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Doktor'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Kullanici.php';

$kullanici_model = new Kullanici($pdo);
$doktor_id = $_SESSION['kullanici']['kullanici_id'];
$mesaj = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $yeni_sifre = $_POST['yeni_sifre'];
    $tekrar_sifre = $_POST['tekrar_sifre'];

    if (empty($yeni_sifre) || empty($tekrar_sifre)) {
        $hata = "Lütfen tüm şifre alanlarını doldurun.";
    } elseif (strlen($yeni_sifre) < 6) {
        $hata = "Şifreniz en az 6 karakter olmalıdır.";
    } elseif ($yeni_sifre !== $tekrar_sifre) {
        $hata = "Girdiğiniz şifreler birbiriyle eşleşmiyor.";
    } else {
        // Şifreyi güncellemek için Kullanici modelindeki metodu çağır
        if ($kullanici_model->sifreGuncelle($doktor_id, $yeni_sifre)) {
            $mesaj = "Şifreniz başarıyla güncellendi! Yeni şifrenizle giriş yapmaya devam edebilirsiniz.";
        } else {
            $hata = "Şifre güncellenirken beklenmedik bir hata oluştu.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifre Değiştirme</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css"> 
</head>
<body>
    <div class="container">
        <h1>Şifre Değiştirme</h1>
        <p><a href="dashboard.php">← Doktor Paneline Geri Dön</a></p>

        <?php if ($mesaj): ?>
            <p class="mesaj-basarili"><?php echo $mesaj; ?></p>
        <?php endif; ?>
        <?php if ($hata): ?>
            <p class="mesaj-hata"><?php echo $hata; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="yeni_sifre">Yeni Şifre (En az 6 karakter):</label>
            <input type="password" id="yeni_sifre" name="yeni_sifre" required minlength="6">

            <label for="tekrar_sifre">Yeni Şifre Tekrar:</label>
            <input type="password" id="tekrar_sifre" name="tekrar_sifre" required>

            <button type="submit">Şifreyi Değiştir</button>
        </form>
    </div>
</body>
</html>