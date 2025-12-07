<?php
// Yetki kontrolü
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Doktor'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/TıbbiKayit.php';
require_once __DIR__ . '/../../models/Randevu.php'; // Randevu modeline ihtiyaç olabilir

$kayit_model = new TıbbiKayit($pdo);
$mesaj = '';
$hata = '';
$randevu_id = $_GET['randevu_id'] ?? null;
$doktor_kullanici_id = $_SESSION['kullanici']['kullanici_id'];

if (empty($randevu_id) || !is_numeric($randevu_id)) {
    die("Hata: Geçersiz randevu ID'si.");
}

// KRİTİK 1: Randevu ID'sine göre hastanın ve randevunun detaylarını çekelim
// (Bu SQL bloğu, kayit_ekle.php'den alınmıştır)
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

$hasta_ad_soyad = $randevu_detay['ad'] . ' ' . $randevu_detay['soyad'];

// KRİTİK 2: Düzenlenecek mevcut tıbbi kaydı çekmek için TıbbiKayit modeline metot ekleyelim
// Varsayım: TıbbiKayit.php'de randevuIdIleKayitGetir($randevu_id) metodu eklendi
$sql_mevcut_kayit = "SELECT muayene_notu, teşhis, reçete_bilgisi, kayit_id 
                     FROM Tıbbi_Kayitlar 
                     WHERE randevu_id = ?";
$stmt_mevcut = $pdo->prepare($sql_mevcut_kayit);
$stmt_mevcut->execute([$randevu_id]);
$mevcut_kayit = $stmt_mevcut->fetch();

if (!$mevcut_kayit) {
    // Kayıt yoksa, kullanıcıyı ekleme sayfasına yönlendir
    header('Location: kayit_ekle.php?randevu_id=' . $randevu_id . '&hata=' . urlencode('Bu randevu için kayıt bulunamadı. Lütfen yeni kayıt girin.'));
    exit;
}

$kayit_id = $mevcut_kayit['kayit_id'];

// KRİTİK 3: Form gönderilmişse (Güncelleme işlemi)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $muayene_notu = trim($_POST['muayene_notu']);
    $teşhis = trim($_POST['teşhis']);
    $reçete_bilgisi = trim($_POST['reçete_bilgisi']);

    if (empty($muayene_notu) || empty($teşhis)) {
        $hata = "Muayene Notu ve Teşhis alanları boş bırakılamaz.";
    } else {
        // TıbbiKayit modeline kayitGuncelle($kayit_id, $not, $teşhis, $reçete) metodu eklenecek
        if ($kayit_model->kayitGuncelle($kayit_id, $muayene_notu, $teşhis, $reçete_bilgisi)) {
            $mesaj = "Tıbbi kayıt başarıyla güncellendi!";
            // Güncel verileri tekrar çek
            $mevcut_kayit = $kayit_model->idIleKayitGetir($kayit_id); // Varsayım: ID ile kayıt çeken metot var
        } else {
            $hata = "Tıbbi kayıt güncellenirken bir hata oluştu.";
        }
    }
}
// POST yoksa veya güncelleme başarısızsa form değerleri çekilen kayıttan gelir
$muayene_notu_value = $_POST['muayene_notu'] ?? $mevcut_kayit['muayene_notu'];
$teşhis_value = $_POST['teşhis'] ?? $mevcut_kayit['teşhis'];
$reçete_bilgisi_value = $_POST['reçete_bilgisi'] ?? $mevcut_kayit['reçete_bilgisi'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tıbbi Kayıt Düzenle</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css"> 
</head>
<body>
    <div class="container">
        <h1>Tıbbi Kayıt Düzenleme</h1>
        <p>Hasta: **<?php echo htmlspecialchars($hasta_ad_soyad); ?>** (Kayıt ID: <?php echo $kayit_id; ?>)</p>
        <p><a href="randevu_listesi.php">← Randevu Takvimine Geri Dön</a></p>

        <?php if ($mesaj): ?>
            <p class="mesaj-basarili"><?php echo $mesaj; ?></p>
        <?php endif; ?>
        <?php if ($hata): ?>
            <p class="mesaj-hata"><?php echo $hata; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="muayene_notu">Muayene Notu (Zorunlu):</label>
            <textarea id="muayene_notu" name="muayene_notu" rows="6" required><?php echo htmlspecialchars($muayene_notu_value); ?></textarea>

            <label for="teşhis">Teşhis (Zorunlu):</label>
            <input type="text" id="teşhis" name="teşhis" required 
                   value="<?php echo htmlspecialchars($teşhis_value); ?>">

            <label for="reçete_bilgisi">Reçete Bilgisi (Opsiyonel):</label>
            <textarea id="reçete_bilgisi" name="reçete_bilgisi" rows="4"><?php echo htmlspecialchars($reçete_bilgisi_value); ?></textarea>

            <button type="submit">Tıbbi Kaydı Güncelle</button>
        </form>
    </div>
</body>
</html>