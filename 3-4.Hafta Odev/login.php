<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Girişi</title>
</head>
<body>
    <h2>Giriş Yap</h2>
    <form action="login_kontrol.php" method="POST">
        <label for="kulad">Kullanıcı Adı:</label>
        <input type="text" id="kulad" name="kulad" required><br><br>

        <label for="sifre">Şifre:</label>
        <input type="password" id="sifre" name="sifre" required><br><br>

        <button type="submit" name="giris">Giriş Yap</button>
    </form>
</body>
</html>