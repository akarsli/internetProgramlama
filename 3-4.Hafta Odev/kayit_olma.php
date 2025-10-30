<?php 

session_start();

require 'db.php';

header("Location: kayit_olma.php")

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kayıt Sayfası</title>
    </head>
    <body>
        <p>Kullanıcı Adı:</p>
        <input>
        <p>Şifre</p>
        <input>

        <button>Kayıt Ol</button>
    </body>
</html>