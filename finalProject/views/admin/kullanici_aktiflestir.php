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
    $hata = "Aktifleştirilecek kullanıcı bulunamadı.";
} else {
    if ($kullanici_model->kullaniciAktiflestir($kullanici_id)) {
        $mesaj = htmlspecialchars($kullanici_bilgi['ad'] . ' ' . $kullanici_bilgi['soyad']) . " kullanıcısı başarıyla **aktif hale** getirildi.";
    } else {
        $hata = "Aktifleştirme işlemi başarısız oldu.";
    }
}

// İşlem bitince listeleme sayfasına yönlendir
header('Location: kullanici_listele.php?mesaj=' . urlencode($mesaj) . '&hata=' . urlencode($hata));
exit;
?>