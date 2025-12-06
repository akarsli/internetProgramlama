<?php
// Yetki kontrolü: Sadece Admin erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Admin'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Kullanici.php';

$kullanici_model = new Kullanici($pdo);
$mesaj = '';
$hata = '';

if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: kullanici_listele.php');
    exit;
}

$kullanici_id = (int)$_GET['id'];
$kullanici_bilgi = $kullanici_model->idIleKullaniciGetir($kullanici_id);

if (!$kullanici_bilgi) {
    $hata = "Pasifleştirilecek kullanıcı bulunamadı.";
} else {
    // Kendi hesabını pasifleştirmeyi engelleme
    if ($kullanici_id == $_SESSION['kullanici']['kullanici_id']) {
        $hata = "Kendi hesabınızı pasifleştiremezsiniz.";
    } elseif ($kullanici_model->kullaniciPasiflestir($kullanici_id)) {
        $mesaj = htmlspecialchars($kullanici_bilgi['ad'] . ' ' . $kullanici_bilgi['soyad']) . " kullanıcısı başarıyla pasifleştirildi.";
    } else {
        $hata = "Pasifleştirme işlemi başarısız oldu.";
    }
}

// İşlem bitince listeleme sayfasına yönlendir
header('Location: kullanici_listele.php?mesaj=' . urlencode($mesaj) . '&hata=' . urlencode($hata));
exit;
?>