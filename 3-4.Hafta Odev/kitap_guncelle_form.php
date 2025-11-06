<?php
session_start();
require 'db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

$kitap_id = $_GET['id'];
$sorgu = $db->prepare("SELECT * FROM kitaplar WHERE kitap_id = :id");
$sorgu->bindParam(':id', $kitap_id);
$sorgu->execute();
$kitap = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$kitap) {
    echo "Kitap bulunamadı.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kitap Güncelle</title>
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <h2>Kitap Güncelle (ID: <?php echo $kitap['kitap_id']; ?>)</h2>
    <form action="kitap_guncelleme_islem.php" method="POST">
        <input type="hidden" name="kitap_id" value="<?php echo $kitap['kitap_id']; ?>">
        
        <label for="ad">Kitap Adı:</label>
        <input type="text" id="ad" name="ad" value="<?php echo htmlspecialchars($kitap['ad']); ?>" required><br><br>
        
        <label for="yazar">Yazar:</label>
        <input type="text" id="yazar" name="yazar" value="<?php echo htmlspecialchars($kitap['yazar']); ?>" required><br><br>
        
        <label for="stok">Stok:</label>
        <input type="number" id="stok" name="stok" value="<?php echo $kitap['stok']; ?>" required><br><br>
        
        <button type="submit" name="guncelle">Güncelle</button>
        <a href="admin_panel.php">Vazgeç</a>
    </form>
</body>
</html>