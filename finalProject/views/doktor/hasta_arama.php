<?php
// Yetki kontrolü: Sadece Doktor erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Doktor'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Kullanici.php';
require_once __DIR__ . '/../../models/TıbbiKayit.php';

$kullanici_model = new Kullanici($pdo);
$kayit_model = new TıbbiKayit($pdo);

$hata = '';
$kayitlar = [];
$hasta_bilgi = null;
$hasta_kullanici_id_arama = null;

// Form gönderilmişse (Hasta Arama)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['arama'])) {
    $arama_terimi = trim($_POST['arama_terimi']);
    
    if (empty($arama_terimi)) {
        $hata = "Lütfen hasta e-postası veya ID'si giriniz.";
    } else {
        // Kullanıcıyı e-posta veya ID ile bulmaya çalış
        
        // Basitleştirilmiş arama: Direkt e-posta ile kullanıcıyı çekelim
        $sql_hasta = "SELECT k.kullanici_id, k.ad, k.soyad, k.e_posta 
                      FROM Kullanicilar k 
                      JOIN Roller r ON k.rol_id = r.rol_id
                      WHERE k.e_posta = ? AND r.rol_adi = 'Hasta'";
        $stmt_hasta = $pdo->prepare($sql_hasta);
        $stmt_hasta->execute([$arama_terimi]);
        $hasta_bilgi = $stmt_hasta->fetch();

        if ($hasta_bilgi) {
            $hasta_kullanici_id_arama = $hasta_bilgi['kullanici_id'];
            
            // Hasta ID'sini Hastalar tablosundan çek
            $sql_get_hasta_id = "SELECT hasta_id FROM Hastalar WHERE kullanici_id = ?";
            $stmt_hasta_id = $pdo->prepare($sql_get_hasta_id);
            $stmt_hasta_id->execute([$hasta_kullanici_id_arama]);
            $hasta_kaydi = $stmt_hasta_id->fetch();

            if ($hasta_kaydi) {
                $hasta_id = $hasta_kaydi['hasta_id'];
                $kayitlar = $kayit_model->hastaKayitlariniGetir($hasta_id);
            } else {
                $hata = "Bu kullanıcının hasta detay kaydı (Hastalar tablosunda) bulunamadı.";
            }

        } else {
            $hata = "Eşleşen aktif Hasta hesabı bulunamadı.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hasta Kayıtları Arama</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css"> 
</head>
<body>
    <div class="container">
        <h1>Hasta Kayıtları Arama ve Geçmişi Görüntüleme</h1>
        <p><a href="dashboard.php">← Doktor Paneline Geri Dön</a></p>

        <?php if ($hata): ?>
            <p class="mesaj-hata"><?php echo $hata; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="arama_terimi">Hasta E-postası ile Ara:</label>
            <input type="text" id="arama_terimi" name="arama_terimi" required 
                   placeholder="Hastanın e-postasını girin"
                   value="<?php echo htmlspecialchars($_POST['arama_terimi'] ?? ''); ?>">

            <button type="submit" name="arama">Hastayı Ara</button>
        </form>
        
        <hr style="margin: 30px 0;">

        <?php if ($hasta_bilgi): ?>
            <h2>Hasta Geçmişi: <?php echo htmlspecialchars($hasta_bilgi['ad'] . ' ' . $hasta_bilgi['soyad']); ?></h2>
            
            <?php if (empty($kayitlar)): ?>
                <p class="mesaj-basarili">Bu hastaya ait geçmiş tıbbi kayıt bulunmamaktadır.</p>
            <?php else: ?>
                <?php foreach ($kayitlar as $kayit): ?>
                <div class="kayit-karti" style="border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 6px;">
                    <h3>Randevu Tarihi: <?php echo date("d.m.Y H:i", strtotime($kayit['randevu_tarihi'])); ?></h3>
                    <p><strong>Teşhis:</strong> <?php echo htmlspecialchars($kayit['teşhis']); ?></p>
                    <p><strong>Kaydı Giren Doktor:</strong> Dr. <?php echo htmlspecialchars($kayit['doktor_ad'] . ' ' . $kayit['doktor_soyad']); ?></p>
                    
                    <h4 style="margin-top: 10px;">Muayene Notu:</h4>
                    <p><?php echo nl2br(htmlspecialchars($kayit['muayene_notu'])); ?></p>
                    
                    <?php if (!empty($kayit['reçete_bilgisi'])): ?>
                    <h4 style="margin-top: 10px;">Reçete Bilgisi:</h4>
                    <p><?php echo nl2br(htmlspecialchars($kayit['reçete_bilgisi'])); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$hata): ?>
            <p class="mesaj-basarili">Arama sonuçlanmadı veya hasta bulunamadı.</p>
        <?php endif; ?>
    </div>
</body>
</html>