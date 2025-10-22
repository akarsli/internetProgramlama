<?php
// db.php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "kullanicilar";

try {
    $db = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
    exit();
}
?>
