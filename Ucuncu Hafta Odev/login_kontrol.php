<?php
// login_kontrol.php

// Session'ı başlatmak ZORUNLUDUR!
session_start();

// Veritabanı bağlantısını dahil et
require 'db.php';

if (isset($_POST['giris'])) {
    $kulad = $_POST['kulad'];
    $sifre = $_POST['sifre'];

    // 1. Kullanıcıyı Veritabanında Bulma
    $sorgu = $db->prepare("SELECT * FROM kullanicilar WHERE kulad = :kulad");
    $sorgu->bindParam(':kulad', $kulad);
    $sorgu->execute();
    $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($kullanici) {
        // 2. Şifreyi Kontrol Etme (HASHlenmiş şifreler için)
        if (password_verify($sifre, $kullanici['sifre'])) {
            // Giriş Başarılı!

            // 3. Session Oluşturma
            $_SESSION['giris_basarili'] = true;
            $_SESSION['kulad'] = $kullanici['kulad'];
            $_SESSION['rol'] = $kullanici['rol'];

            // 4. Role Göre Yönlendirme
            if ($kullanici['rol'] === 'admin') {
                header("Location: admin_panel.php"); // Admin sayfasına git
                exit;
            } else if ($kullanici['rol'] === 'uye') {
                header("Location: uye_sayfasi.php"); // Üye sayfasına git
                exit;
            }

        } else {
            // Şifre yanlış
            echo "Kullanıcı adı veya şifre yanlış.";
        }
    } else {
        // Kullanıcı bulunamadı
        echo "Kullanıcı adı veya şifre yanlış.";
    }
}
?>