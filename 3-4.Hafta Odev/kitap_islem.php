<?php
session_start();
require 'db.php';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['ekle'])) {
    $ad = $_POST['ad'];
    $yazar = $_POST['yazar'];
    $stok = $_POST['stok'];

    $sorgu = $db->prepare("INSERT INTO kitaplar (ad, yazar, stok) VALUES (:ad, :yazar, :stok)");
    $sorgu->bindParam(':ad', $ad);
    $sorgu->bindParam(':yazar', $yazar);
    $sorgu->bindParam(':stok', $stok);

    if ($sorgu->execute()) {
        header("Location: admin_panel.php?durum=eklendi");
    } else {
        echo "Kitap eklenirken bir hata oluştu.";
    }
    exit;
}

if (isset($_GET['sil_id'])) {
    $kitap_id = $_GET['sil_id'];

    $sorgu = $db->prepare("DELETE FROM kitaplar WHERE kitap_id = :id");
    $sorgu->bindParam(':id', $kitap_id);

    if ($sorgu->execute()) {
        header("Location: admin_panel.php?durum=silindi");
    } else {
        echo "Kitap silinirken bir hata oluştu.";
    }
    exit;
}
?>