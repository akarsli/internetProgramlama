<?php
// Bu dosya AJAX isteği ile çağrılacaktır.

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Randevu.php';

$randevu_model = new Randevu($pdo);

header('Content-Type: application/json');

// POST verilerini al
$doktor_id = $_POST['doktor_id'] ?? null;
$tarih = $_POST['tarih'] ?? null;

// Gerekli kontroller
if (empty($doktor_id) || empty($tarih) || !is_numeric($doktor_id)) {
    echo json_encode(['hata' => 'Geçersiz doktor ID veya tarih.']);
    exit;
}

// Dolu saatleri çek (Örn: ['10:00:00', '14:30:00'])
$dolu_saatler_mysql_format = $randevu_model->doluSaatleriGetir($doktor_id, $tarih);

// MySQL formatını (HH:MM:SS) HH:MM formatına dönüştür
$dolu_saatler = array_map(function($saat) {
    return substr($saat, 0, 5); // 10:00:00 -> 10:00
}, $dolu_saatler_mysql_format);


// Çalışma saatlerini (09:00-23:00, 30 dk aralıklarla) üret
$baslangic = strtotime('09:00');
$bitis = strtotime('23:00'); // 23:00'e kadar randevu alabilir

$tum_saatler = [];
while ($baslangic < $bitis) {
    $saat_str = date('H:i', $baslangic);
    $tum_saatler[] = $saat_str;
    $baslangic = strtotime('+30 minutes', $baslangic);
}

// Saatleri dolu listesiyle karşılaştır
$saat_durumu = [];
foreach ($tum_saatler as $saat) {
    // Dolu saatler dizisinde (HH:MM formatında) var mı?
    $is_dolu = in_array($saat, $dolu_saatler);
    
    $saat_durumu[] = [
        'saat' => $saat,
        'dolu' => $is_dolu
    ];
}

echo json_encode(['saatler' => $saat_durumu]);
?>