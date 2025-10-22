<?php
// admin_panel.php
session_start();

// Giriş yapılmamışsa veya rolü 'admin' değilse login sayfasına yönlendir
if (!isset($_SESSION['giris_basarili']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['kulad'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
</head>
<body>
    <h2>Hoşgeldin, <?php echo htmlspecialchars($username); ?>! (Admin)</h2>
    <p>Bu sadece adminlerin görebileceği bir sayfadır.</p>
    <p><a href="cikis.php">Çıkış Yap</a></p>
</body>
</html>