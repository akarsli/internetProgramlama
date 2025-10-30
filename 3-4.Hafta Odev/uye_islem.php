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

// ----------------------------------------------------
// A) YENİ KULLANICI EKLEME İŞLEMİ (POST İSTEĞİ) - CREATE
// ----------------------------------------------------
if (isset($_POST['uye_ekle'])) {
    $kulad = $_POST['kulad'];
    $sifre = $_POST['sifre'];
    $rol = $_POST['rol']; // Formdan gelen rol (admin, uye, pasif)

    // 1. Şifreyi Hash'le (Güvenlik)
    $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);
    
    // 2. Kullanıcı adının benzersizliğini kontrol et
    $kontrol_sorgu = $db->prepare("SELECT kulad FROM kullanicilar WHERE kulad = :kulad");
    $kontrol_sorgu->bindParam(':kulad', $kulad);
    $kontrol_sorgu->execute();

    if ($kontrol_sorgu->rowCount() > 0) {
        // Kullanıcı adı zaten varsa hata ver
        header("Location: admin_panel.php?hata=kullanici_adi_mevcut");
        exit;
    }

    // 3. Veritabanına Ekleme
    $ekle_sorgu = $db->prepare("INSERT INTO kullanicilar (kulad, sifre, rol) VALUES (:kulad, :sifre, :rol)");
    
    $ekle_sorgu->bindParam(':kulad', $kulad);
    $ekle_sorgu->bindParam(':sifre', $hashed_sifre);
    $ekle_sorgu->bindParam(':rol', $rol); // Seçilen rolü kaydet

    if ($ekle_sorgu->execute()) {
        header("Location: admin_panel.php?durum=uye_eklendi");
    } else {
        header("Location: admin_panel.php?hata=ekleme_hata");
    }
    exit;
}

// ----------------------------------------------------
// B) ÜYE DURUM GÜNCELLEME İŞLEMİ (GET İSTEĞİ) - UPDATE
// ----------------------------------------------------
if (isset($_GET['id']) && isset($_GET['durum'])) {
    $uye_id = $_GET['id'];
    $durum = $_GET['durum']; // 'pasif' veya 'uye'

    // Admin'in kendini pasifleştirmesini engelle
    if ($uye_id == $_SESSION['id'] && $durum == 'pasif') {
         header("Location: admin_panel.php?hata=kendini_pasif_yapamazsin");
         exit;
    }

    // Güvenlik: Sadece beklenen değerleri işleme al
    if ($durum === 'pasif' || $durum === 'uye') {
        $yeni_rol = $durum;
    } else {
        // Beklenmeyen bir değer gelirse, işlemi durdur
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

// Ne POST ne de GET isteği gelmediyse Admin paneline yönlendir
else {
    header("Location: admin_panel.php");
    exit;
}
?>