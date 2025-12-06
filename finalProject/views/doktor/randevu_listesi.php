<?php
// Yetki kontrolü
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Doktor'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Randevu.php';

$randevu_model = new Randevu($pdo);
$doktor_kullanici_id = $_SESSION['kullanici']['kullanici_id'];

// Randevuları çekerken doktorun kendi Kullanici ID'sini kullanıyoruz.
// Randevu modelindeki SQL, Hastalar tablosu üzerinden hastanın adını çekecek şekilde güncellenmiştir.
$randevular = $randevu_model->doktorRandevulariniGetir($doktor_kullanici_id);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Randevu Takvimi</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css"> 
</head>
<body>
    <div class="container">
        <h1>Randevu Takvimim</h1>
        <p><a href="dashboard.php">← Doktor Paneline Geri Dön</a></p>

        <?php if (isset($_GET['mesaj']) && $_GET['mesaj']): ?>
            <p class="mesaj-basarili"><?php echo htmlspecialchars($_GET['mesaj']); ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['hata']) && $_GET['hata']): ?>
            <p class="mesaj-hata"><?php echo htmlspecialchars($_GET['hata']); ?></p>
        <?php endif; ?>
        
        <?php if (empty($randevular)): ?>
            <p class="mesaj-basarili">Oluşturulmuş randevunuz bulunmamaktadır.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Hasta</th>
                        <th>Tarih ve Saat</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($randevular as $randevu): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($randevu['hasta_ad'] . ' ' . $randevu['hasta_soyad']); ?></td>
                        <td><?php echo date("d.m.Y H:i", strtotime($randevu['tarih_saat'])); ?></td>
                        <td><?php echo htmlspecialchars($randevu['durum']); ?></td>
                        <td>
                            <?php if ($randevu['durum'] == 'Planlandı'): ?>
                                <a href="randevu_guncelle.php?id=<?php echo $randevu['randevu_id']; ?>&durum=Onaylandı" style="color: var(--primary-color);">Onayla</a> |
                                <a href="randevu_guncelle.php?id=<?php echo $randevu['randevu_id']; ?>&durum=Reddedildi" style="color: red;">Reddet</a>
                            
                            <?php elseif ($randevu['durum'] == 'Onaylandı'): ?>
                                <a href="randevu_guncelle.php?id=<?php echo $randevu['randevu_id']; ?>&durum=Tamamlandı" style="color: green;">Tamamlandı</a>
                            
                            <?php elseif ($randevu['durum'] == 'Tamamlandı'): ?>
                                <a href="kayit_ekle.php?randevu_id=<?php echo $randevu['randevu_id']; ?>" style="color: blue;">Tıbbi Kayıt Ekle</a>
                                
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