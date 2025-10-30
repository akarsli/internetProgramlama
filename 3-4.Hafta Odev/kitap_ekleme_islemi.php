<?php
session_start();
require 'db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['kitap_ekle'])) {
    $ad = $_POST['kitap_ad'];
    $yazar = $_POST['yazar'];
    $stok = $_POST['stok'];

    $sorgu = $db->prepare("INSERT INTO kitaplar (ad, yazar, stok) VALUES (:ad, :yazar, :stok)");

    $sorgu->bindParam(':ad', $ad);
    $sorgu->bindParam(':yazar', $yazar);
    $sorgu->bindParam(':stok', $stok);

    if ($sorgu->execute()) {
        header("Location: admin_panel.php?durum=eklendi");
        exit;
    } else {
        echo "Kitap eklenirken bir hata oluştu.";
    }
}
?>