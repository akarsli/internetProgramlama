<?php
// Yetki kontrolü: Sadece Admin erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Admin'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Kullanici.php';
require_once __DIR__ . '/../../config/uzmanliklar.php';

$kullanici_model = new Kullanici($pdo);
$mesaj = '';
$hata = '';
$kullanici_id = $_GET['id'] ?? null;

if (empty($kullanici_id) || !is_numeric($kullanici_id)) {
    die("Hata: Geçersiz kullanıcı ID'si.");
}

// Tüm rolleri çek (Selectbox için)
$roller = $kullanici_model->tumRolleriGetir();

// 1. Mevcut bilgileri çek
$kullanici_bilgi = $kullanici_model->idIleKullaniciGetir($kullanici_id);

if (!$kullanici_bilgi) {
    die("Hata: Düzenlenecek kullanıcı bulunamadı.");
}

// 2. Form gönderilmişse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
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
    elseif (isset($_POST['guncelle'])) {
        $ad = trim($_POST['ad']);
        $soyad = trim($_POST['soyad']);
        $e_posta = trim($_POST['e_posta']);
        $telefon = trim($_POST['telefon']);
        $rol_id = (int)$_POST['rol_id'];
        
        // Eğer rol Doktor değilse uzmanlık alanını null yapalım
        $secilen_rol_bilgi = null;
        foreach($roller as $r) if($r['rol_id'] == $rol_id) $secilen_rol_bilgi = $r;
        
        $uzmanlik_alani = ($secilen_rol_bilgi && $secilen_rol_bilgi['rol_adi'] === 'Doktor') 
                          ? trim($_POST['uzmanlik_alani'] ?? null) 
                          : null;

        if (empty($ad) || empty($soyad) || empty($e_posta) || empty($rol_id)) {
            $hata = "Ad, Soyad, E-posta ve Rol alanları boş bırakılamaz.";
        } elseif ($kullanici_model->adminKullaniciGuncelle($kullanici_id, $ad, $soyad, $e_posta, $telefon, $rol_id, $uzmanlik_alani)) {
            $mesaj = htmlspecialchars($kullanici_bilgi['ad'] . ' ' . $kullanici_bilgi['soyad']) . " kullanıcısının bilgileri başarıyla güncellendi!";
            $kullanici_bilgi = $kullanici_model->idIleKullaniciGetir($kullanici_id);
        } else {
            $hata = "Güncelleme sırasında bir hata oluştu.";
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
        <form method="POST" id="duzenlemeFormu">
            <label for="ad">Ad:</label>
            <input type="text" id="ad" name="ad" required value="<?php echo htmlspecialchars($kullanici_bilgi['ad']); ?>">

            <label for="soyad">Soyad:</label>
            <input type="text" id="soyad" name="soyad" required value="<?php echo htmlspecialchars($kullanici_bilgi['soyad']); ?>">
            
            <label for="e_posta">E-posta:</label>
            <input type="email" id="e_posta" name="e_posta" required value="<?php echo htmlspecialchars($kullanici_bilgi['e_posta']); ?>">

            <label for="telefon">Telefon:</label>
            <input type="text" id="telefon" name="telefon" value="<?php echo htmlspecialchars($kullanici_bilgi['telefon'] ?? ''); ?>">
                   
            <label for="rol_id">Rol:</label>
            <select id="rol_id" name="rol_id" required onchange="doktorKontrol()">
                <?php foreach ($roller as $rol): ?>
                    <option value="<?php echo htmlspecialchars($rol['rol_id']); ?>" 
                            data-rol-adi="<?php echo htmlspecialchars($rol['rol_adi']); ?>"
                        <?php echo ($kullanici_bilgi['rol_adi'] == $rol['rol_adi']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($rol['rol_adi']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div id="uzmanlik_bolumu" style="<?php echo ($kullanici_bilgi['rol_adi'] !== 'Doktor') ? 'display:none;' : ''; ?>">
                <label for="uzmanlik_alani">Uzmanlık Alanı:</label>
                <select id="uzmanlik_alani" name="uzmanlik_alani">
                    <option value="">Lütfen Uzmanlık Alanını Seçiniz</option>
                    <?php foreach ($UZMANLIK_ALANLARI as $uzmanlik): ?>
                        <?php 
                            $current_value = $_POST['uzmanlik_alani'] ?? $kullanici_bilgi['uzmanlik_alani'];
                            $selected = ($current_value === $uzmanlik) ? 'selected' : '';
                        ?>
                        <option value="<?php echo htmlspecialchars($uzmanlik); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($uzmanlik); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <br><br>
            <button type="submit" name="guncelle">Bilgileri ve Rolü Güncelle</button>
        </form>
        
        <hr>

        <h2>Şifreyi Sıfırla</h2>
        <form method="POST">
            <label for="yeni_sifre">Yeni Şifre (En az 6 karakter):</label>
            <input type="password" id="yeni_sifre" name="yeni_sifre" required minlength="6">
            <label for="tekrar_sifre">Yeni Şifre Tekrar:</label>
            <input type="password" id="tekrar_sifre" name="tekrar_sifre" required>
            <button type="submit" name="sifre_guncelle" style="background-color: #f39c12;">Şifreyi Güncelle</button>
        </form>
    </div>

    <script>
    function doktorKontrol() {
        var rolSelect = document.getElementById('rol_id');
        var uzmanlikBolumu = document.getElementById('uzmanlik_bolumu');
        // Seçilen opsiyonun içindeki data-rol-adi değerini kontrol et
        var secilenRolAdi = rolSelect.options[rolSelect.selectedIndex].getAttribute('data-rol-adi');
        
        if (secilenRolAdi === 'Doktor') {
            uzmanlikBolumu.style.display = 'block';
        } else {
            uzmanlikBolumu.style.display = 'none';
            document.getElementById('uzmanlik_alani').value = ""; // Doktor değilse alanı sıfırla
        }
    }
    </script>
</body>
</html>