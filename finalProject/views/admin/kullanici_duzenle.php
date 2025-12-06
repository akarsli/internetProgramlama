<?php
// Yetki kontrolü: Sadece Admin erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Admin'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Kullanici.php';

$kullanici_model = new Kullanici($pdo);
$mesaj = '';
$hata = '';
$kullanici_id = $_GET['id'] ?? null;

if (empty($kullanici_id) || !is_numeric($kullanici_id)) {
    die("Hata: Geçersiz kullanıcı ID'si.");
}

// Tüm rolleri çek (Selectbox için)
$roller = $kullanici_model->tumRolleriGetir();

// 1. Mevcut bilgileri çek (Formu önceden doldurmak için)
$kullanici_bilgi = $kullanici_model->idIleKullaniciGetir($kullanici_id);

if (!$kullanici_bilgi) {
    die("Hata: Düzenlenecek kullanıcı bulunamadı.");
}

// 2. Form gönderilmişse (Güncelleme işlemleri)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- Şifre Güncelleme İşlemi ---
    if (isset($_POST['sifre_guncelle'])) {
        $yeni_sifre = $_POST['yeni_sifre'];
        $tekrar_sifre = $_POST['tekrar_sifre'];

        if (empty($yeni_sifre) || empty($tekrar_sifre)) {
            $hata = "Lütfen yeni şifre alanlarını doldurun.";
        } elseif (strlen($yeni_sifre) < 6) {
            $hata = "Şifre en az 6 karakter olmalıdır.";
        } elseif ($yeni_sifre !== $tekrar_sifre) {
            $hata = "Girdiğiniz şifreler eşleşmiyor.";
        } elseif ($kullanici_model->sifreGuncelle($kullanici_id, $yeni_sifre)) {
            $mesaj = htmlspecialchars($kullanici_bilgi['ad']) . " kullanıcısının şifresi başarıyla güncellendi!";
        } else {
            $hata = "Şifre güncellenirken beklenmedik bir hata oluştu.";
        }
    } 
    
    // --- Temel Bilgi Güncelleme İşlemi ---
    elseif (isset($_POST['guncelle'])) {
        $ad = trim($_POST['ad']);
        $soyad = trim($_POST['soyad']);
        $e_posta = trim($_POST['e_posta']);
        $telefon = trim($_POST['telefon']);
        $rol_id = (int)$_POST['rol_id'];

        if (empty($ad) || empty($soyad) || empty($e_posta) || empty($rol_id)) {
            $hata = "Ad, Soyad, E-posta ve Rol alanları boş bırakılamaz.";
        } elseif ($kullanici_model->adminKullaniciGuncelle($kullanici_id, $ad, $soyad, $e_posta, $telefon, $rol_id)) {
            $mesaj = htmlspecialchars($kullanici_bilgi['ad'] . ' ' . $kullanici_bilgi['soyad']) . " kullanıcısının bilgileri ve rolü başarıyla güncellendi!";
            
            // Güncel verileri tekrar çek
            $kullanici_bilgi = $kullanici_model->idIleKullaniciGetir($kullanici_id);
        } else {
            $hata = "Güncelleme sırasında bir hata oluştu. E-posta adresi zaten kullanılıyor olabilir.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Düzenle</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css"> 
</head>
<body>
    <div class="container">
        <h1>Kullanıcı Düzenle: <?php echo htmlspecialchars($kullanici_bilgi['ad'] . ' ' . $kullanici_bilgi['soyad']); ?></h1>
        <p><a href="kullanici_listele.php">← Kullanıcı Listesine Geri Dön</a></p>

        <?php if ($mesaj): ?>
            <p class="mesaj-basarili"><?php echo $mesaj; ?></p>
        <?php endif; ?>
        <?php if ($hata): ?>
            <p class="mesaj-hata"><?php echo $hata; ?></p>
        <?php endif; ?>

        <h2>Temel Bilgileri Düzenle</h2>
        <form method="POST">
            <label for="ad">Ad:</label>
            <input type="text" id="ad" name="ad" required 
                   value="<?php echo htmlspecialchars($kullanici_bilgi['ad']); ?>">

            <label for="soyad">Soyad:</label>
            <input type="text" id="soyad" name="soyad" required 
                   value="<?php echo htmlspecialchars($kullanici_bilgi['soyad']); ?>">
            
            <label for="e_posta">E-posta:</label>
            <input type="email" id="e_posta" name="e_posta" required 
                   value="<?php echo htmlspecialchars($kullanici_bilgi['e_posta']); ?>">

            <label for="telefon">Telefon:</label>
            <input type="text" id="telefon" name="telefon" 
                   value="<?php echo htmlspecialchars($kullanici_bilgi['telefon'] ?? ''); ?>">
                   
            <label for="rol_id">Rol:</label>
            <select id="rol_id" name="rol_id" required>
                <?php foreach ($roller as $rol): ?>
                    <option value="<?php echo htmlspecialchars($rol['rol_id']); ?>"
                        <?php 
                            if ($kullanici_bilgi['rol_adi'] == $rol['rol_adi']) {
                                echo 'selected';
                            }
                        ?>>
                        <?php echo htmlspecialchars($rol['rol_adi']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <button type="submit" name="guncelle">Bilgileri ve Rolü Güncelle</button>
        </form>
        
        <hr>

        <h2 style="margin-top: 40px;">Şifreyi Sıfırla</h2>
        <p style="color: grey;">Bu işlem kullanıcının şifresini yöneticinin belirlediği yeni bir şifreyle değiştirir.</p>
        <form method="POST">
            <label for="yeni_sifre">Yeni Şifre (En az 6 karakter):</label>
            <input type="password" id="yeni_sifre" name="yeni_sifre" required minlength="6">

            <label for="tekrar_sifre">Yeni Şifre Tekrar:</label>
            <input type="password" id="tekrar_sifre" name="tekrar_sifre" required>

            <button type="submit" name="sifre_guncelle" style="background-color: #f39c12;">Şifreyi Güncelle/Sıfırla</button>
        </form>
        
    </div>
</body>
</html>