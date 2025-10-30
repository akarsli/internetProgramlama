<?php
session_start();
require 'db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['uye_ekle'])) {
    $kulad = $_POST['kulad'];
    $sifre = $_POST['sifre'];
    
    $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);
    $rol = 'uye';
    $kontrol_sorgu = $db->prepare("SELECT kulad FROM kullanicilar WHERE kulad = :kulad");
    $kontrol_sorgu->bindParam(':kulad', $kulad);
    $kontrol_sorgu->execute();

    if ($kontrol_sorgu->rowCount() > 0) {
        header("Location: admin_panel.php?hata=kullanici_adi_mevcut");
        exit;
    }

    $ekle_sorgu = $db->prepare("INSERT INTO kullanicilar (kulad, sifre, rol) VALUES (:kulad, :sifre, :rol)");
    
    $ekle_sorgu->bindParam(':kulad', $kulad);
    $ekle_sorgu->bindParam(':sifre', $hashed_sifre);
    $ekle_sorgu->bindParam(':rol', $rol);

    if ($ekle_sorgu->execute()) {
        header("Location: admin_panel.php?durum=uye_eklendi");
    } else {
        header("Location: admin_panel.php?hata=ekleme_hata");
    }
    exit;
}

if (isset($_POST['uye_ekle'])) {
    $kulad = $_POST['kulad'];
    $sifre = $_POST['sifre'];
    $rol = $_POST['rol'];
    
    $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);
    
    $kontrol_sorgu = $db->prepare("SELECT kulad FROM kullanicilar WHERE kulad = :kulad");
    $kontrol_sorgu->bindParam(':kulad', $kulad);
    $kontrol_sorgu->execute();

    if ($kontrol_sorgu->rowCount() > 0) {
        header("Location: admin_panel.php?hata=kullanici_adi_mevcut");
        exit;
    }

    $ekle_sorgu = $db->prepare("INSERT INTO kullanicilar (kulad, sifre, rol) VALUES (:kulad, :sifre, :rol)");
    
    $ekle_sorgu->bindParam(':kulad', $kulad);
    $ekle_sorgu->bindParam(':sifre', $hashed_sifre);
    $ekle_sorgu->bindParam(':rol', $rol);

    if ($ekle_sorgu->execute()) {
        header("Location: admin_panel.php?durum=uye_eklendi");
    } else {
        header("Location: admin_panel.php?hata=ekleme_hata");
    }
    exit;
}
?>