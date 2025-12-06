<?php
// Yetki kontrolü
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Hasta'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/TıbbiKayit.php';

$kayit_model = new TıbbiKayit($pdo);
$hasta_kullanici_id = $_SESSION['kullanici']['kullanici_id'];

// Hasta ID'sini al (Daha önce randevu_listele.php'de yaptığımız gibi)
$sql_get_hasta_id = "SELECT hasta_id FROM Hastalar WHERE kullanici_id = ?";
$stmt_hasta_id = $pdo->prepare($sql_get_hasta_id);
$stmt_hasta_id->execute([$hasta_kullanici_id]);
$hasta_kaydi = $stmt_hasta_id->fetch();

$kayitlar = [];
if ($hasta_kaydi) {
    $hasta_id = $hasta_kaydi['hasta_id'];
    $kayitlar = $kayit_model->hastaKayitlariniGetir($hasta_id);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tıbbi Kayıt Geçmişi</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css"> 
</head>
<body>
    <div class="container">
        <h1>Tıbbi Kayıt Geçmişi</h1>
        <p><a href="dashboard.php">← Hasta Paneline Geri Dön</a></p>

        <?php if (empty($kayitlar)): ?>
            <p class="mesaj-basarili">Geçmiş tıbbi kaydınız bulunmamaktadır.</p>
        <?php else: ?>
            <?php foreach ($kayitlar as $kayit): ?>
            <div class="kayit-karti">
                <h3>Randevu Tarihi: <?php echo date("d.m.Y H:i", strtotime($kayit['randevu_tarihi'])); ?></h3>
                <p><strong>Doktor:</strong> Dr. <?php echo htmlspecialchars($kayit['doktor_ad'] . ' ' . $kayit['doktor_soyad']); ?></p>
                <p><strong>Teşhis:</strong> <?php echo htmlspecialchars($kayit['teşhis']); ?></p>
                
                <div class="kayit-detay">
                    <h4>Muayene Notu:</h4>
                    <p><?php echo nl2br(htmlspecialchars($kayit['muayene_notu'])); ?></p>
                    
                    <?php if (!empty($kayit['reçete_bilgisi'])): ?>
                    <h4>Reçete Bilgisi:</h4>
                    <p><?php echo nl2br(htmlspecialchars($kayit['reçete_bilgisi'])); ?></p>
                    <?php endif; ?>
                </div>
                <p class="kayit-tarihi">Kayıt Giriş Tarihi: <?php echo date("d.m.Y", strtotime($kayit['kayit_tarihi'])); ?></p>
            </div>
            <hr>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>