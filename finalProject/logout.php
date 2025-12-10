<?php
session_start();
session_unset();    // Tüm oturum değişkenlerini kaldır
session_destroy();  // Oturumu yok et
header('Location: homepage.php'); // Giriş sayfasına yönlendir
exit;
?>