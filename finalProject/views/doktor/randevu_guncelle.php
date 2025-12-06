<?php
// Yetki kontrolü: Sadece Doktor erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Doktor'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Randevu.php';

$randevu_model = new Randevu($pdo);

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['durum'])) {
    header('Location: randevu_listesi.php');
    exit;
}

$randevu_id = (int)$_GET['id'];
$yeni_durum = $_GET['durum']; // Örn: Onaylandı, Tamamlandı, Reddedildi

// Geçerli durumları kontrol etme (Güvenlik)
$gecerli_durumlar = ['Onaylandı', 'Tamamlandı', 'Reddedildi'];
if (!in_array($yeni_durum, $gecerli_durumlar)) {
    header('Location: randevu_listesi.php');
    exit;
}

if ($randevu_model->randevuDurumGuncelle($randevu_id, $yeni_durum)) {
    $mesaj = "Randevu durumu başarıyla **{$yeni_durum}** olarak güncellendi.";
} else {
    $hata = "Durum güncellenirken bir hata oluştu.";
}

// İşlem bitince listeleme sayfasına yönlendir
header('Location: randevu_listesi.php?mesaj=' . urlencode($mesaj) . '&hata=' . urlencode($hata));
exit;
?>