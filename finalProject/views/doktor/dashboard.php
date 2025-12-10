<?php
// Yetki kontrolü
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Doktor'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Randevu.php'; // Yeni metotlar burada

$doktor_ad = $_SESSION['kullanici']['ad'] ?? 'Doktor';
$doktor_id = $_SESSION['kullanici']['kullanici_id'];

$randevu_model = new Randevu($pdo);

// Dinamik verileri çekme
$bugunku_randevu_sayisi = $randevu_model->bugunkuRandevuSayisiniGetir($doktor_id);
$eksik_kayit_sayisi = $randevu_model->eksikKayitSayisiniGetir($doktor_id);

// Mesajı oluşturma mantığı
$dashboard_mesaji = '';
$mesaj_sinifi = 'mesaj-bilgi';

if ($bugunku_randevu_sayisi > 0) {
    $dashboard_mesaji .= "Bugün {$bugunku_randevu_sayisi} adet planlanmış randevunuz bulunmaktadır. Lütfen takviminizi kontrol edin.";
} else {
    $dashboard_mesaji .= "Bugün için planlanmış aktif randevunuz bulunmamaktadır. ";
    $mesaj_sinifi = 'mesaj-basarili';
}

if ($eksik_kayit_sayisi > 0) {
    $dashboard_mesaji .= " Ayrıca, kayıt girişi eksik olan {$eksik_kayit_sayisi} adet 'TAMAMLANDI' randevunuz var. Lütfen tamamlayın! ⚠️";
    $mesaj_sinifi = 'mesaj-hata'; // Eksik kayıt varsa uyarı mesajı ver
} elseif ($bugunku_randevu_sayisi === 0) {
    $dashboard_mesaji = "Tebrikler, bugün için aktif randevunuz ve eksik kaydınız bulunmamaktadır. Rahat bir gün!";
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Doktor Kontrol Paneli</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css">
</head>
<body>
    <div class="container">
        <h1>Hoş Geldiniz, Dr. <?php echo htmlspecialchars($doktor_ad); ?></h1>
        <div class="<?php echo $mesaj_sinifi; ?>" style="padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $dashboard_mesaji; ?>
        </div>

        <h2>İşlemler</h2>
        <ul>
            <li><a href="/../../homepage.php">🏠 Ana Sayfaya Git</a></li>
            <li><a href="randevu_listesi.php">🗓️ Randevularım</a></li>
            <li><a href="hasta_arama.php">🔍 Hasta Kayıtları Arama ve Oluşturma</a></li>
            <li><a href="bilgi_duzenle.php">✏️ Kişisel Bilgileri Düzenle</a></li>
            <li><a href="../../logout.php">➡️ Çıkış Yap</a></li>
        </ul>
    </div>
</body>
</html>