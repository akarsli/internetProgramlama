<?php
// cikis.php
session_start();

// Tüm session değişkenlerini sil
$_SESSION = array();

// Session'ı tamamen yok et
session_destroy();

// Kullanıcıyı login sayfasına yönlendir
header("Location: index.php");
exit;
?>
