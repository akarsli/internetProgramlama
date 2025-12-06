<?php
// Yetki kontrolü: Sadece Hasta erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Hasta'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Randevu.php';

$randevu_model = new Randevu($pdo);

if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    // ID yoksa veya geçersizse geri yönlendir
    header('Location: randevu_listele.php');
    exit;
}

$randevu_id = (int)$_GET['id'];
$mesaj = '';
$hata = '';

// Güvenlik: Randevunun gerçekten bu hastaya ait olup olmadığını kontrol etme
// Randevu modelinizde randevuGetir($id) metodunu kullanarak bu kontrolü yapmanız önerilir.

// Randevuyu kalıcı olarak silme işlemini çağırıyoruz
if ($randevu_model->randevuKalıcıSil($randevu_id)) {
    $mesaj = "Randevunuz kalıcı olarak silinmiştir.";
} else {
    $hata = "Randevuyu silme işlemi başarısız. Lütfen tekrar deneyin.";
}

// İşlem bitince listeleme sayfasına yönlendir
header('Location: randevu_listele.php?mesaj=' . urlencode($mesaj) . '&hata=' . urlencode($hata));
exit;
?>