<?php
// db.php
$servername = "localhost";
$username = "root"; // Veritabanı kullanıcı adınız
$password = "root";     // Veritabanı şifreniz
$dbname = "kullanicilar"; // Veritabanı adınız

try {
    $db = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // Hata modunu ayarlama
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Bağlantı başarılı"; // Test için
} catch(PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
    exit(); // Hata varsa çalışmayı durdur
}
?>
