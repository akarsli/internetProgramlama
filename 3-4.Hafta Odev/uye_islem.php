<?php
// uye_islem.php
session_start();
require 'db.php';

// GENEL YETKİLENDİRME: Admin değilse login sayfasına yönlendir
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['sil_id'])) {
    $silinecek_id = $_GET['sil_id'];
    
    // Güvenlik Kontrolü: Admin'in kendini silmesini engelle
    if ($silinecek_id == $_SESSION['id']) {
        header("Location: admin_panel.php?hata=kendini_silemezsin");
        exit;
    }

    $sorgu = $db->prepare("DELETE FROM kullanicilar WHERE id = :id");
    $sorgu->bindParam(':id', $silinecek_id);

    if ($sorgu->execute()) {
        header("Location: admin_panel.php?durum=uye_silindi");
    } else {
        header("Location: admin_panel.php?hata=silme_hata");
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

if (isset($_GET['id']) && isset($_GET['durum'])) {
    $uye_id = $_GET['id'];
    $durum = $_GET['durum'];

    if ($uye_id == $_SESSION['id'] && $durum == 'pasif') {
         header("Location: admin_panel.php?hata=kendini_pasif_yapamazsin");
         exit;
    }

    if ($durum === 'pasif' || $durum === 'uye') {
        $yeni_rol = $durum;
    } else {
        header("Location: admin_panel.php?hata=gecersiz_durum");
        exit;
    }
    

    $sorgu = $db->prepare("UPDATE kullanicilar SET rol = :rol WHERE id = :id");
    
    $sorgu->bindParam(':rol', $yeni_rol);
    $sorgu->bindParam(':id', $uye_id);

    if ($sorgu->execute()) {
        header("Location: admin_panel.php?durum=uyedurumu_guncellendi");
    } else {
        header("Location: admin_panel.php?hata=guncelleme_hata");
    }
    exit;
} 

else {
    header("Location: admin_panel.php");
    exit;
}
?>