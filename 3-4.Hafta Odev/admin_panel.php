<?php
session_start();
require 'db.php';

if (!isset($_SESSION['giris_basarili']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['kulad'];

$kitap_sorgu = $db->prepare("SELECT * FROM kitaplar");
$kitap_sorgu->execute();
$kitaplar = $kitap_sorgu->fetchAll(PDO::FETCH_ASSOC);

$uye_sorgu = $db->prepare("SELECT id, kulad, rol FROM kullanicilar");
$uye_sorgu->execute();
$uyeler = $uye_sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <style>table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }</style>
</head>
<body>
    <h1>Hoşgeldin, <?php echo htmlspecialchars($username); ?>! (ADMIN)</h1>
    <p><a href="cikis.php">Çıkış Yap</a></p>

    <h2>Kitap Ekle</h2>
    <form action="kitap_islem.php" method="POST">
        <input type="text" name="ad" placeholder="Kitap Adı" required>
        <input type="text" name="yazar" placeholder="Yazar" required>
        <input type="number" name="stok" placeholder="Stok" required>
        <button type="submit" name="ekle">Ekle</button>
    </form>
    <hr>

    <h2>Kitap Listesi</h2>
    <table>
        <tr>
            <th>ID</th><th>Ad</th><th>Yazar</th><th>Stok</th><th>İşlem</th>
        </tr>
        <?php foreach ($kitaplar as $kitap): ?>
        <tr>
            <td><?php echo $kitap['kitap_id']; ?></td>
            <td><?php echo $kitap['ad']; ?></td>
            <td><?php echo $kitap['yazar']; ?></td>
            <td><?php echo $kitap['stok']; ?></td>
            <td>
                <a href="kitap_guncelle_form.php?id=<?php echo $kitap['kitap_id']; ?>">Güncelle</a> |
                <a href="kitap_islem.php?sil_id=<?php echo $kitap['kitap_id']; ?>" onclick="return confirm('Emin misiniz?');">Sil</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <hr>

    <h2>Kullanıcı Ekle</h2>
    <form action="uye_islem.php" method="POST">
        <input type="text" name="kulad" placeholder="Kullanıcı Adı" required><br><br>
        
        <input type="password" name="sifre" placeholder="Şifre" required><br><br>
        
        <label for="rol">Rol Seçin:</label>
        <select name="rol" id="rol">
            <option value="uye">Üye</option>
            <option value="admin">Admin</option>
            <option value="pasif">Pasif/Engelli</option>
        </select><br><br>
        
        <button type="submit" name="uye_ekle">Kullanıcı Ekle</button> 
    </form>
    <hr>

    <h2>Üye Yönetimi</h2>
    <table>
        <tr>
            <th>ID</th><th>Kullanıcı Adı</th><th>Rol</th><th>İşlem</th>
        </tr>
        <?php foreach ($uyeler as $uye): ?>
        <tr>
            <td><?php echo $uye['id']; ?></td>
            <td><?php echo $uye['kulad']; ?></td>
            <td><?php echo $uye['rol']; ?></td>
            <td>
                <?php if ($uye['rol'] === 'uye'): ?>
                    <a href="uye_islem.php?id=<?php echo $uye['id']; ?>&durum=pasif">Pasif Yap</a>
                <?php elseif ($uye['rol'] === 'pasif'): ?>
                    <a href="uye_islem.php?id=<?php echo $uye['id']; ?>&durum=uye">Aktif Yap</a>
                <?php else: ?>
                    <span style="color:gray;">Admin İşlenemez</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>