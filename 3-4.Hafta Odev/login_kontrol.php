<?php
session_start();
require 'db.php';

if (isset($_POST['giris'])) {
    $kulad = $_POST['kulad'];
    $sifre = $_POST['sifre'];

    $sorgu = $db->prepare("SELECT * FROM kullanicilar WHERE kulad = :kulad");
    $sorgu->bindParam(':kulad', $kulad);
    $sorgu->execute();
    $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($kullanici) {
        if (password_verify($sifre, $kullanici['sifre'])) {
            $_SESSION['giris_basarili'] = true;
            $_SESSION['kulad'] = $kullanici['kulad'];
            $_SESSION['rol'] = $kullanici['rol'];
            $_SESSION['id'] = $kullanici['id'];

            if ($kullanici['rol'] === 'admin') {
                header("Location: admin_panel.php");
                exit;
            } else if ($kullanici['rol'] === 'uye') {
                header("Location: uye_sayfasi.php");
                exit;
            }

        } else {
            echo "Kullanıcı adı veya şifre yanlış.";
        }
    } else {
        echo "Kullanıcı adı veya şifre yanlış.";
    }
}
?>