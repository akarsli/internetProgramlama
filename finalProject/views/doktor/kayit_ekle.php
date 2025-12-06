<?php
// Yetki kontrolü
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Doktor'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Randevu.php';
require_once __DIR__ . '/../../models/TıbbiKayit.php';
require_once __DIR__ . '/../../models/Kullanici.php'; // Yardımcı model

$randevu_model = new Randevu($pdo);
$kayit_model = new TıbbiKayit($pdo);

$mesaj = '';
$hata = '';
$randevu_id = $_GET['randevu_id'] ?? null;
$doktor_kullanici_id = $_SESSION['kullanici']['kullanici_id'];

if (empty($randevu_id) || !is_numeric($randevu_id)) {
    // Bu kontrolün geçmesi için her zaman linkten gelinmeli.
    die("Hata: Geçersiz randevu ID'si. Randevu listesi üzerinden gelmelisiniz.");
}

// 1. Randevu ve Hasta detayını çek
$sql_randevu_detay = "SELECT r.hasta_id, h.kullanici_id AS hasta_kullanici_id, u.ad, u.soyad 
                      FROM Randevular r
                      JOIN Hastalar h ON r.hasta_id = h.hasta_id
                      JOIN Kullanicilar u ON h.kullanici_id = u.kullanici_id
                      WHERE r.randevu_id = ?";
$stmt_detay = $pdo->prepare($sql_randevu_detay);
$stmt_detay->execute([$randevu_id]);
$randevu_detay = $stmt_detay->fetch();

if (!$randevu_detay) {
    die("Hata: Randevu detayı bulunamadı.");
}

$hasta_id = $randevu_detay['hasta_id'];
$hasta_ad_soyad = $randevu_detay['ad'] . ' ' . $randevu_detay['soyad'];

// 2. YÖNLENDİRME KONTROLÜ: Mevcut tıbbi kayıt var mı?
$sql_mevcut_kayit = "SELECT kayit_id FROM Tıbbi_Kayitlar WHERE randevu_id = ?";
$stmt_mevcut = $pdo->prepare($sql_mevcut_kayit);
$stmt_mevcut->execute([$randevu_id]);
$mevcut_kayit = $stmt_mevcut->fetch();

if ($mevcut_kayit) {
    // KAYIT VARSA: Ekleme değil, Güncelleme sayfasına yönlendir.
    header('Location: kayit_duzenle.php?randevu_id=' . $randevu_id . '&mesaj=' . urlencode('Bu randevu için kayıt zaten girilmiş. Lütfen mevcut kaydı düzenleyin.'));
    exit;
}

// 3. POST İŞLEMLERİ (Sadece kayıt yoksa ve form gönderilmişse çalışır)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $muayene_notu = trim($_POST['muayene_notu']);
    $teşhis = trim($_POST['teşhis']);
    $reçete_bilgisi = trim($_POST['reçete_bilgisi']);

    if (empty($muayene_notu) || empty($teşhis)) {
        $hata = "Muayene Notu ve Teşhis alanları boş bırakılamaz.";
    } else {
        if ($kayit_model->kayitEkle($randevu_id, $doktor_kullanici_id, $hasta_id, $muayene_notu, $teşhis, $reçete_bilgisi)) {
            $mesaj = "Tıbbi kayıt başarıyla oluşturuldu!";
            
            // Başarılı kayıttan sonra listeye geri yönlendir
            header('Location: randevu_listesi.php?mesaj=' . urlencode($mesaj));
            exit;

        } else {
            // Bu kısım, beklenmedik bir SQL hatası (örn. sunucu hatası) yakalarsa çalışır.
            $hata = "Tıbbi kayıt eklenirken beklenmedik bir veritabanı hatası oluştu.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tıbbi Kayıt Ekle</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css"> 
</head>
<body>
    <div class="container">
        <h1>Tıbbi Kayıt Ekleme</h1>
        <p>Hasta: **<?php echo htmlspecialchars($hasta_ad_soyad); ?>** (Randevu ID: <?php echo $randevu_id; ?>)</p>
        <p><a href="randevu_listesi.php">← Randevu Takvimine Geri Dön</a></p>

        <?php if ($mesaj): ?>
            <p class="mesaj-basarili"><?php echo $mesaj; ?></p>
        <?php endif; ?>
        <?php if ($hata): ?>
            <p class="mesaj-hata"><?php echo $hata; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="muayene_notu">Muayene Notu (Zorunlu):</label>
            <textarea id="muayene_notu" name="muayene_notu" rows="6" required><?php echo htmlspecialchars($_POST['muayene_notu'] ?? ''); ?></textarea>

            <label for="teşhis">Teşhis (Zorunlu):</label>
            <input type="text" id="teşhis" name="teşhis" required 
                   value="<?php echo htmlspecialchars($_POST['teşhis'] ?? ''); ?>">

            <label for="reçete_bilgisi">Reçete Bilgisi (Opsiyonel):</label>
            <textarea id="reçete_bilgisi" name="reçete_bilgisi" rows="4"><?php echo htmlspecialchars($_POST['reçete_bilgisi'] ?? ''); ?></textarea>

            <button type="submit">Tıbbi Kaydı Kaydet</button>
        </form>
    </div>
</body>
</html>