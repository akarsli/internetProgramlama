<?php
// Yetki kontrolü: Sadece 'Hasta' rolü erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Hasta'); 

// Oturumdaki Hasta bilgilerini al
$hasta = $_SESSION['kullanici']; 
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hasta Kontrol Paneli</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css">
</head>
<body>
    <h1>Hasta Kontrol Paneli, Hoş geldiniz <?php echo htmlspecialchars($hasta['ad']); ?>!</h1>
    <p>Buradan randevularınızı yönetebilir ve tıbbi geçmişinizi görüntüleyebilirsiniz.</p>

    <h2>İşlemler</h2>
    <ul>
        <li><a href="/../../homepage.php">🏠 Ana Sayfaya Git</a></li>
        <li><a href="randevu_al.php">🗓️ Yeni Randevu Al</a></li>
        <li><a href="randevu_listele.php">📋 Mevcut Randevuları Görüntüle</a></li> 
        <li><a href="kayit_gecmisi.php">🩺 Tıbbi Kayıtlarımı Görüntüle</a></li>
        <li><a href="bilgi_duzenle.php">✏️ Kişisel Bilgileri Düzenle</a></li>
        <li><a href="../../logout.php">➡️ Çıkış Yap</a></li>
    </ul>
</body>
</html>