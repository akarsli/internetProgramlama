<?php
// Yetki kontrolü: Sadece 'Admin' rolü erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Admin'); 

$admin = $_SESSION['kullanici']; 
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Kontrol Paneli</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css">
</head>
<body>
    <div class="container">
        <h1>Admin Kontrol Paneli, Hoş geldiniz <?php echo htmlspecialchars($admin['ad'] . ' ' . $admin['soyad']); ?>!</h1>
        
        <h2>Yönetim Menüsü</h2>
        <ul>
            <li><a href="/../../homepage.php">🏠 Ana Sayfaya Git</a></li>
            <li><a href="admin_ekle.php">👮‍♀️ Admin Ekle</a></li>
            <li><a href="doktor_ekle.php">🩺 Doktor Ekle</a></li>
            <li><a href="kullanici_listele.php">👥 Tüm Kullanıcıları Listele</a></li>
            <li><a href="../../logout.php">➡️ Çıkış Yap</a></li>
        </ul>
    </div>
    </body>
</html>