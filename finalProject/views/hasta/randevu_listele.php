<?php
// Yetki kontrolü
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Hasta'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Randevu.php';

$randevu_model = new Randevu($pdo);
$hasta_kullanici_id = $_SESSION['kullanici']['kullanici_id'];
$mesaj = $_GET['mesaj'] ?? '';
$hata = $_GET['hata'] ?? '';


// KRİTİK: Kullanici ID'den Hastalar tablosundaki doğru hasta_id'yi çekme
// Randevu modelinin doğru çalışması için Hastalar tablosundaki ID'ye ihtiyacımız var.
$sql_get_hasta_id = "SELECT hasta_id FROM Hastalar WHERE kullanici_id = ?";
$stmt_hasta_id = $pdo->prepare($sql_get_hasta_id);
$stmt_hasta_id->execute([$hasta_kullanici_id]);
$hasta_kaydi = $stmt_hasta_id->fetch();

$randevular = [];
if (!$hasta_kaydi) {
    // Hasta detay kaydı yoksa randevu listesi boş kalır
    $hata = $hata ?: "Randevularınızı görüntülemek için hasta detay kaydınızın olması gerekmektedir. Yöneticiye başvurun.";
} else {
    $hasta_id = $hasta_kaydi['hasta_id']; 
    // Randevuları çekerken Hastalar tablosundaki doğru hasta_id'yi kullanıyoruz
    $randevular = $randevu_model->hastaRandevulariniGetir($hasta_id); 
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Randevu Geçmişi</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css"> 
</head>
<body>
    <div class="container">
        <h1>Randevu Geçmişim</h1>
        <p><a href="dashboard.php">← Hasta Paneline Geri Dön</a></p>

        <?php if ($mesaj): ?>
            <p class="mesaj-basarili"><?php echo htmlspecialchars($mesaj); ?></p>
        <?php endif; ?>
        <?php if ($hata): ?>
            <p class="mesaj-hata"><?php echo htmlspecialchars($hata); ?></p>
        <?php endif; ?>
        
        <?php if (empty($randevular)): ?>
            <p class="mesaj-basarili">Henüz oluşturulmuş bir randevunuz bulunmamaktadır. <a href="randevu_al.php">Hemen Randevu Alın</a></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Doktor</th>
                        <th>Tarih ve Saat</th>
                        <th>Durum</th>
                        <th>İşlemler</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($randevular as $randevu): ?>
                    <tr>
                        <td>Dr. <?php echo htmlspecialchars($randevu['doktor_ad'] . ' ' . $randevu['doktor_soyad']); ?></td>
                        <td><?php echo date("d.m.Y H:i", strtotime($randevu['tarih_saat'])); ?></td>
                        <td><?php echo htmlspecialchars($randevu['durum']); ?></td>
                        <td>
                            <?php if ($randevu['durum'] == 'Planlandı'): ?>
                                <a href="randevu_iptal.php?id=<?php echo $randevu['randevu_id']; ?>" 
                                   style="color: red; font-weight: bold;"
                                   onclick="return confirm('UYARI: Randevuyu kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz ve randevu iptal edilir.');">
                                    İptal Et ve Sil
                                </a>
                            <?php else: ?>
                                <?php echo htmlspecialchars($randevu['durum']); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>