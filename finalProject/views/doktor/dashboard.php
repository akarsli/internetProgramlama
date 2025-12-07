<?php
// Yetki kontrolü: Sadece 'Doktor' rolü erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Doktor'); 

// Oturumdaki Doktor bilgilerini al
$doktor = $_SESSION['kullanici']; 
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
        <h1>Doktor Kontrol Paneli, Hoş geldiniz Dr. <?php echo htmlspecialchars($doktor['soyad']); ?>!</h1>
        <p>Bugünkü randevularınız ve hasta kayıtları burada.</p>

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