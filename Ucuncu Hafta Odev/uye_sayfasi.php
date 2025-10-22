<?php

session_start();

if (!isset($_SESSION['giris_basarili']) || $_SESSION['rol'] !== 'uye') {
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
    <h2>Hoşgeldin, <?php echo htmlspecialchars($username); ?>! (Üye)</h2>
    <p>Bu tüm üyelerin görebileceği bir sayfadır.</p>
    <p><a href="cikis.php">Çıkış Yap</a></p>
</body>
</html>