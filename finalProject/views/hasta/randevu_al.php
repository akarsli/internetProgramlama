<?php
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Hasta'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Kullanici.php'; 
require_once __DIR__ . '/../../controllers/RandevuController.php';

$kullanici_model = new Kullanici($pdo);
$randevuController = new RandevuController($pdo);
$doktorlar = $kullanici_model->doktorlariGetir(); 

$hasta_kullanici_id = $_SESSION['kullanici']['kullanici_id'];
$mesaj = '';
$hata = '';

// YENİ KONTROL: Başarılı POST verilerini saklamak için
$varsayilan_doktor_id = $_POST['doktor_id'] ?? '';
$varsayilan_tarih = $_POST['tarih'] ?? '';
$varsayilan_saat = $_POST['saat'] ?? '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Controller'ı çağır
    $sonuc = $randevuController->olustur($_POST, $hasta_kullanici_id);

    if ($sonuc['basarili']) {
        $mesaj = $sonuc['mesaj'];
        
        // BAŞARILI İŞLEM SONRASI: Form alanlarını temizle (uyarıları engellemek için)
        $varsayilan_doktor_id = '';
        $varsayilan_tarih = '';
        $varsayilan_saat = '';
        
    } else {
        $hata = $sonuc['hata'];
        
        // HATA OLMASI DURUMUNDA: Kullanıcının girdiği veriyi koru
        $varsayilan_doktor_id = $_POST['doktor_id'];
        $varsayilan_tarih = $_POST['tarih'];
        $varsayilan_saat = $_POST['saat'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Randevu Al</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css"> 
</head>
<body>
    <div class="container">
        <h1>Yeni Randevu Al</h1>
        <p><a href="dashboard.php">← Hasta Paneline Geri Dön</a></p>

        <?php if ($mesaj): ?>
            <p class="mesaj-basarili"><?php echo $mesaj; ?></p>
        <?php endif; ?>
        <?php if ($hata): ?>
            <p class="mesaj-hata"><?php echo $hata; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="doktor_id">Doktor Seçimi:</label>
            <select id="doktor_id" name="doktor_id" required>
                <option value="">Lütfen bir doktor seçin</option>
                <?php foreach ($doktorlar as $doktor): ?>
                    <option value="<?php echo htmlspecialchars($doktor['kullanici_id']); ?>"
                        <?php echo (isset($_POST['doktor_id']) && $_POST['doktor_id'] == $doktor['kullanici_id']) ? 'selected' : ''; ?>>
                        Dr. <?php echo htmlspecialchars($doktor['ad'] . ' ' . $doktor['soyad']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="tarih">Tarih:</label>
            <input type="date" id="tarih" name="tarih" required 
                   value="<?php echo htmlspecialchars($_POST['tarih'] ?? ''); ?>">

            <label for="saat">Saat:</label>
            <input type="time" id="saat" name="saat" step="900" required
                   value="<?php echo htmlspecialchars($_POST['saat'] ?? ''); ?>"> <button type="submit">Randevu Al</button>
        </form>
    </div>
</body>
</html>