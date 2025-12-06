<?php
// Oturumu başlat (Eğer daha önce başlatılmadıysa)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Kullanıcının belirli bir role sahip olup olmadığını kontrol eder.
 * Rol eşleşmezse veya kullanıcı giriş yapmamışsa ana sayfaya (index.php) yönlendirir.
 * @param string $gerekli_rol Sayfaya erişmek için gereken rol adı (örneğin: 'Admin').
 */
function yetki_kontrol($gerekli_rol) {
    // 1. Kullanıcı giriş yapmış mı? (Oturum var mı?)
    if (!isset($_SESSION['kullanici'])) {
        // Giriş yapmamışsa Login sayfasına yönlendir
        header('Location: ../../index.php');
        exit;
    }

    $kullanici_rol = $_SESSION['kullanici']['rol_adi'];

    // 2. Rol kontrolü
    if ($kullanici_rol !== $gerekli_rol) {
        // Rol eşleşmiyorsa (Örn: Doktor Admin sayfasına girmeye çalışırsa)
        // Güvenlik amaçlı yine Login sayfasına yönlendiriyoruz.
        header('Location: ../../index.php');
        exit;
    }
}
?>