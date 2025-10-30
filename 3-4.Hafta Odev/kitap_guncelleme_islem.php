<?php
session_start();
require 'db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin' || !isset($_POST['guncelle'])) {
    header("Location: login.php");
    exit;
}

$kitap_id = $_POST['kitap_id'];
$ad = $_POST['ad'];
$yazar = $_POST['yazar'];
$stok = $_POST['stok'];

$sorgu = $db->prepare("UPDATE kitaplar SET ad = :ad, yazar = :yazar, stok = :stok WHERE kitap_id = :id");

$sorgu->bindParam(':ad', $ad);
$sorgu->bindParam(':yazar', $yazar);
$sorgu->bindParam(':stok', $stok);
$sorgu->bindParam(':id', $kitap_id);

if ($sorgu->execute()) {
    header("Location: admin_panel.php?durum=guncellendi");
} else {
    echo "Kitap güncellenirken bir hata oluştu.";
}
exit;
?>