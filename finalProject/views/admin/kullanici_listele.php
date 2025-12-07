<?php
// Yetki kontrolü: Sadece 'Admin' rolü erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Admin'); 

// Bağlantı nesnesini (pdo) ve Kullanici modelini dahil et
require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Kullanici.php';

$kullanici_model = new Kullanici($pdo);
$kullanicilar = $kullanici_model->tumKullanicilariGetir();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Listesi</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .rol-admin { color: red; font-weight: bold; }
        .rol-doktor { color: blue; }
        .rol-hasta { color: green; }
    </style>
</head>
<body>
    <h1>Tüm Kullanıcılar (Toplam: <?php echo count($kullanicilar); ?>)</h1>
    <p><a href="dashboard.php">← Yönetici Paneline Geri Dön</a></p>

    <?php if (empty($kullanicilar)): ?>
        <p>Sistemde kayıtlı kullanıcı bulunmamaktadır.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>Rol</th>
                    <th>Uzmanlık Alanı</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kullanicilar as $kullanici): ?>
                <tr>
                    <td><?php echo htmlspecialchars($kullanici['kullanici_id']); ?></td>
                    <td><?php echo htmlspecialchars($kullanici['ad'] . ' ' . $kullanici['soyad']); ?></td>
                    <td><?php echo htmlspecialchars($kullanici['e_posta']); ?></td>
                    <td><?php echo htmlspecialchars(string: $kullanici['telefon'] ?? '-'); ?></td>
                    <td class="rol-<?php echo strtolower($kullanici['rol_adi']); ?>">
                        <?php echo htmlspecialchars($kullanici['rol_adi']); ?>
                    </td>
                    <td>
                            <?php 
                            if ($kullanici['rol_adi'] == 'Doktor' && !empty($kullanici['uzmanlik_alani'])) {
                                echo htmlspecialchars($kullanici['uzmanlik_alani']);
                            } elseif ($kullanici['rol_adi'] == 'Doktor') {
                                echo 'Tanımsız';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    <td><?php echo $kullanici['aktif_mi'] ? 'Aktif' : 'Pasif'; ?></td>
                    <td>
                        <a href="kullanici_duzenle.php?id=<?php echo $kullanici['kullanici_id']; ?>">Düzenle (Update)</a> 
                        
                        <?php if ($kullanici['aktif_mi']): ?>
                            | <a href="kullanici_pasiflestir.php?id=<?php echo $kullanici['kullanici_id']; ?>" 
                               style="color: red;"
                               onclick="return confirm('Kullanıcı pasifleştirilecektir. Devam etmek istiyor musunuz?');">
                                Pasifleştir
                            </a>
                        <?php else: ?>
                            | <a href="kullanici_aktiflestir.php?id=<?php echo $kullanici['kullanici_id']; ?>" 
                               style="color: green; font-weight: bold;"
                               onclick="return confirm('Kullanıcı tekrar aktif hale getirilecektir. Devam etmek istiyor musunuz?');">
                                Aktifleştir
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>