<?php
session_start();

if (!isset($_SESSION['giris_basarili']) || ($_SESSION['rol'] !== 'uye' && $_SESSION['rol'] !== 'admin')) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['kulad'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Üye Sayfası</title>
</head>
<body>
    <h1>Hoşgeldin, <?php echo htmlspecialchars($username); ?>! (ÜYE)</h1>
    <p>Bu, tüm üyelerin ve adminlerin görebileceği bir sayfadır.</p>
    <?php if ($_SESSION['rol'] === 'admin'): ?>
        <p><a href="admin_panel.php">Admin Paneline Dön</a></p>
    <?php endif; ?>
    <p><a href="cikis.php">Çıkış Yap</a></p>
</body>
</html>