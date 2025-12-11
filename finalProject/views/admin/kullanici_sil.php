<?php
// Yetki kontrolü: Sadece Admin erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Admin'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Kullanici.php';

$kullanici_model = new Kullanici($pdo);
$mesaj = '';
$hata = '';

// 1. Gerekli ID parametresi var mı kontrolü
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: kullanici_listele.php?hata=' . urlencode('Geçersiz kullanıcı ID\'si.'));
    exit;
}

$kullanici_id = (int)$_GET['id'];
$kullanici_bilgi = $kullanici_model->idIleKullaniciGetir($kullanici_id);

// 2. Kullanıcı gerçekten var mı kontrolü
if (!$kullanici_bilgi) {
    $hata = "Silinecek kullanıcı bulunamadı.";
} else {
    // 3. Güvenlik Kontrolü: Kendi hesabını silmeyi engelleme
    if ($kullanici_id == $_SESSION['kullanici']['kullanici_id']) {
        $hata = "Kendi hesabınızı kalıcı olarak silemezsiniz.";
    } else {
        $rol_adi = $kullanici_bilgi['rol_adi'];

        // 4. Kalıcı silme işlemi (İlişkili kayıtları silen metot çağrılır)
        if ($kullanici_model->kullaniciKalıcıSil($kullanici_id, $rol_adi)) {
            $mesaj = htmlspecialchars($kullanici_bilgi['ad'] . ' ' . $kullanici_bilgi['soyad']) . " kullanıcısı ve ilişkili tüm kayıtları başarıyla kalıcı olarak silindi.";
        } else {
            // Eğer veritabanı silme işlemi bir hata döndürürse (örn. beklenmedik Foreign Key hatası)
            $hata = "Kalıcı silme işlemi başarısız oldu. İlişkili kayıtlar nedeniyle bir sorun olabilir.";
        }
    }
}

// 5. İşlem bitince listeleme sayfasına yönlendir
header('Location: kullanici_listele.php?mesaj=' . urlencode($mesaj) . '&hata=' . urlencode($hata));
exit;
?>