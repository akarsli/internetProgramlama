<?php
// Yetki kontrolü: Sadece Admin erişebilir
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Admin'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Kullanici.php';

$kullanici_model = new Kullanici($pdo);
$mesaj = $_GET['mesaj'] ?? '';
$hata = $_GET['hata'] ?? '';

// Tüm kullanıcıları ve rolleri çek
$kullanicilar = $kullanici_model->tumKullanicilariGetir();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tüm Kullanıcılar</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css"> 
    <style>
        /* Admin Listeleme Sayfasına Özgü İyileştirmeler */

        /* Tablonun Genel Görünümü */

        .container-table table {
            width: 100%;
            border-collapse: separate; 
            border-spacing: 0;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            background-color: white;
            border-radius: 8px; 
            overflow: hidden; 
        }

        /* Başlık Satırı */
        .container-table th {
            background-color: #2c7efc; /* Klinik Mavi */
            color: white;
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 0.5px;
            position: sticky; 
            top: 0;
        }

        /* Vücut Satırları */
        .container-table td {
            padding: 12px 20px;
            border-bottom: 1px solid #f0f0f0; 
            vertical-align: middle;
            word-break: break-word;
        }

        /* Tek ve Çift Satır Renkleri */
        .container-table tbody tr:nth-child(even) {
            background-color: #f9fbfd; 
        }

        /* Satır Hover Etkisi */
        .container-table tbody tr:hover {
            background-color: #f0f8ff; 
            transition: background-color 0.3s ease;
        }

        /* Son Satırın Çizgisini Kaldırma */
        .container-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Durum Göstergeleri (Aktif/Pasif) */
        .status-active, .status-inactive {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 700;
        }

        .status-active {
            background-color: #e6f7e9; 
            color: #38a169; 
        }

        .status-inactive {
            background-color: #fde8e8; 
            color: #e53e3e; 
        }

        /* İşlem Butonları ve Link Stilleri */
        .container-table td a {
            text-decoration: none;
            font-weight: 600;
            margin-right: 10px;
            padding: 5px 0;
            transition: color 0.2s ease;
        }

        .container-table td a:last-child {
            margin-right: 0;
        }

        /* Düzenle Butonu */
        .container-table td a[href*="duzenle"] {
            color: #2c7efc; /* Mavi */
        }

        /* Pasifleştir Butonu */
        .container-table td a[href*="pasiflestir"] {
            color: orange;
        }

        /* Sil Butonu (Kırmızı Vurgu) */
        .container-table td a[href*="sil"] {
            color: #e53e3e; 
        }
    </style>
</head>
<body>
    <div class="container-table">
        <h1>Tüm Kullanıcılar (Admin)</h1>
        <p><a href="dashboard.php">← Yönetici Paneline Geri Dön</a></p>

        <?php if ($mesaj): ?>
            <p class="mesaj-basarili"><?php echo htmlspecialchars($mesaj); ?></p>
        <?php endif; ?>
        <?php if ($hata): ?>
            <p class="mesaj-hata"><?php echo htmlspecialchars($hata); ?></p>
        <?php endif; ?>

        <?php if (empty($kullanicilar)): ?>
            <p class="mesaj-basarili">Sistemde kayıtlı kullanıcı bulunmamaktadır.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
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
                        <td><?php echo htmlspecialchars($kullanici['rol_adi']); ?></td>
                        
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
                        
                        <td>
                            <?php if ($kullanici['aktif_mi']): ?>
                                <span class="status-active">Aktif</span>
                            <?php else: ?>
                                <span class="status-inactive">İnaktif</span>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <a href="kullanici_duzenle.php?id=<?php echo $kullanici['kullanici_id']; ?>">Düzenle</a> 
                            
                            <?php if ($kullanici['aktif_mi']): ?>
                                | <a href="kullanici_pasiflestir.php?id=<?php echo $kullanici['kullanici_id']; ?>" 
                                   onclick="return confirm('Kullanıcı pasifleştirilecektir. Devam etmek istiyor musunuz?');">
                                    İnaktif Yap
                                </a>
                            <?php else: ?>
                                | <a href="kullanici_aktiflestir.php?id=<?php echo $kullanici['kullanici_id']; ?>" 
                                   onclick="return confirm('Kullanıcı tekrar aktif hale getirilecektir. Devam etmek istiyor musunuz?');">
                                    Aktifleştir
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($kullanici['kullanici_id'] != $_SESSION['kullanici']['kullanici_id']): ?>
                            | <a href="kullanici_sil.php?id=<?php echo $kullanici['kullanici_id']; ?>" 
                               style="color: red; font-weight: bold;"
                               onclick="return confirm('TEHLİKE: Bu kullanıcıyı ve tüm ilişkili kayıtlarını kalıcı olarak silmek üzeresiniz. Bu işlem geri ALINAMAZ. Emin misiniz?');">
                                Sil
                            </a>
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