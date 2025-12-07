<?php
// Yetki kontrolü
require_once __DIR__ . '/../../config/yetki_kontrol.php';
yetki_kontrol('Hasta'); 

require_once __DIR__ . '/../../config/db_baglanti.php';
require_once __DIR__ . '/../../models/Kullanici.php'; 
require_once __DIR__ . '/../../controllers/RandevuController.php'; // Randevu oluşturma mantığı burada

$kullanici_model = new Kullanici($pdo);
$randevuController = new RandevuController($pdo); 
$doktorlar = $kullanici_model->doktorlariGetir(); 

$hasta_kullanici_id = $_SESSION['kullanici']['kullanici_id'];
$mesaj = '';
$hata = '';

// Varsayılan değerler (form gönderildiğinde değeri korumak için)
$varsayilan_doktor_id = $_POST['doktor_id'] ?? '';
$varsayilan_tarih = $_POST['tarih'] ?? '';
$varsayilan_saat = $_POST['saat'] ?? '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Controller'ı çağır
    $sonuc = $randevuController->olustur($_POST, $hasta_kullanici_id);

    if ($sonuc['basarili']) {
        $mesaj = $sonuc['mesaj'];
        
        // BAŞARILI İŞLEM SONRASI: Form alanlarını temizle
        $varsayilan_doktor_id = '';
        $varsayilan_tarih = '';
        $varsayilan_saat = '';
        
    } else {
        $hata = $sonuc['hata'];
        
        // HATA OLMASI DURUMUNDA: Kullanıcının girdiği veriyi koru
        $varsayilan_doktor_id = $_POST['doktor_id'] ?? '';
        $varsayilan_tarih = $_POST['tarih'] ?? '';
        $varsayilan_saat = $_POST['saat'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Randevu Al</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/dashboard_style.css"> 
    <style>
        /* CSS stilleri, butonları modern ve tıklanabilir yapmak için */
        #tarihSecimi, #saatSecimi {
            display: none; /* Başlangıçta gizle */
            margin-top: 20px;
        }
        .time-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .time-slots button {
            padding: 10px 15px;
            border: 1px solid #2c7efc; /* Primary Color */
            background-color: #fff;
            color: #2c7efc;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.2s;
            font-weight: 600;
        }
        .time-slots button:hover:not(:disabled) {
            background-color: #2c7efc;
            color: #fff;
        }
        .time-slots button:disabled {
            background-color: #f0f0f0;
            color: #ccc;
            border-color: #ddd;
            cursor: not-allowed;
            opacity: 0.6;
            text-decoration: line-through;
        }
        /* Seçili Buton Stili */
        .time-slots button.selected {
            background-color: #2c7efc;
            color: #fff;
        }
        /* Tarih Butonları için özel stil */
        .time-slots button.date-btn {
            width: 100px; /* Butonların genişliğini sabitle */
            height: 50px;
            line-height: 1.1;
            padding: 5px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Yeni Randevu Al</h1>
        <p><a href="dashboard.php">← Hasta Paneline Geri Dön</a></p>

        <?php if ($mesaj): ?>
            <p class="mesaj-basarili"><?php echo $mesaj; ?></p>
        <?php endif; ?>
        <?php if ($hata): ?>
            <p class="mesaj-hata"><?php echo $hata; ?></p>
        <?php endif; ?>

        <form method="POST" id="randevuForm">
            <label for="doktor_id">Doktor Seçimi:</label>
            <select id="doktor_id" name="doktor_id" required>
                <option value="">Lütfen bir doktor seçin</option>
                <?php foreach ($doktorlar as $doktor): ?>
                    <option value="<?php echo htmlspecialchars($doktor['kullanici_id']); ?>"
                        <?php echo ($varsayilan_doktor_id == $doktor['kullanici_id']) ? 'selected' : ''; ?>>
                        Dr. <?php echo htmlspecialchars($doktor['ad'] . ' ' . $doktor['soyad']); ?> (<?php echo htmlspecialchars($doktor['uzmanlik_alani']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <div id="tarihSecimi">
                <label>Tarih Seçimi (7 Gün İçinde):</label>
                <div class="time-slots" id="takvim_container">
                    </div>
                <input type="hidden" id="tarih" name="tarih" value="<?php echo htmlspecialchars($varsayilan_tarih); ?>" required>
            </div>

            <div id="saatSecimi">
                <label>Saat Seçimi (09:00 - 23:00):</label>
                <div class="time-slots" id="saat_container">
                    </div>
                <input type="hidden" id="saat" name="saat" value="<?php echo htmlspecialchars($varsayilan_saat); ?>" required>
            </div>
            
            <br>
            <button type="submit" id="submitButton" disabled>Randevu Al</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const doktorSelect = document.getElementById('doktor_id');
            const tarihSecimiDiv = document.getElementById('tarihSecimi');
            const saatSecimiDiv = document.getElementById('saatSecimi');
            const takvimContainer = document.getElementById('takvim_container');
            const saatContainer = document.getElementById('saat_container');
            const tarihInput = document.getElementById('tarih');
            const saatInput = document.getElementById('saat');
            const submitButton = document.getElementById('submitButton');
            
            // PHP'den gelen varsayılan değerleri al
            let selectedTarih = '<?php echo $varsayilan_tarih; ?>';
            let selectedSaat = '<?php echo $varsayilan_saat; ?>';

            // --- A. 7 GÜNLÜK TAKVİM OLUŞTURMA LOGİĞİ ---
            function createTakvim(doktorId) {
                const today = new Date();
                takvimContainer.innerHTML = ''; 
                
                for (let i = 0; i < 7; i++) {
                    const nextDay = new Date(today);
                    nextDay.setDate(today.getDate() + i);
                    
                    const tarihStr = nextDay.toISOString().split('T')[0]; // YYYY-MM-DD
                    const gunAdi = nextDay.toLocaleDateString('tr-TR', { weekday: 'short' });
                    const ayGun = nextDay.toLocaleDateString('tr-TR', { day: 'numeric', month: 'numeric' });

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.classList.add('date-btn');
                    button.dataset.tarih = tarihStr;
                    button.innerHTML = `${gunAdi} <br> ${ayGun}`;
                    
                    // Eğer varsayılan tarih varsa butonu seçili getir
                    if (selectedTarih === tarihStr) {
                         button.classList.add('selected');
                    }

                    button.addEventListener('click', function() {
                        // Seçimi kaldır ve yenisini ekle
                        document.querySelectorAll('.date-btn').forEach(btn => btn.classList.remove('selected'));
                        this.classList.add('selected');
                        
                        selectedTarih = tarihStr;
                        tarihInput.value = tarihStr;
                        saatContainer.innerHTML = ''; // Yeni tarih seçildi, saatleri temizle
                        saatInput.value = ''; // Saati sıfırla
                        saatSecimiDiv.style.display = 'block'; // Saat alanını göster
                        submitButton.disabled = true;

                        fetchSaatler(doktorId, tarihStr);
                    });
                    takvimContainer.appendChild(button);
                }
                tarihSecimiDiv.style.display = 'flex'; // Tarih alanını göster
            }

            // --- B. DOLU SAATLERİ KONTROL ETME LOGİĞİ (AJAX) ---
            function fetchSaatler(doktorId, tarih) {
                saatContainer.innerHTML = 'Saatler yükleniyor...';
                
                // AJAX isteği için views/hasta/saatleri_cek.php dosyasını çağır
                fetch('saatleri_cek.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `doktor_id=${doktorId}&tarih=${tarih}`
                })
                .then(response => response.json())
                .then(data => {
                    saatContainer.innerHTML = '';
                    if (data.hata) {
                         saatContainer.innerHTML = `<p class="mesaj-hata">${data.hata}</p>`;
                         return;
                    }
                    
                    if (data.saatler.length === 0) {
                        saatContainer.innerHTML = `<p class="mesaj-basarili">Bu tarihte uygun saat bulunmamaktadır.</p>`;
                        return;
                    }

                    let anyAvailable = false;

                    data.saatler.forEach(slot => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.textContent = slot.saat;
                        
                        // Doluysa butonu pasif ve çizili yap
                        if (slot.dolu) {
                            button.disabled = true;
                            button.title = 'Bu saat doludur.';
                        } else {
                            anyAvailable = true;
                        }
                        
                        // Eğer form hatadan geri döndüyse ve bu saat seçiliydiysa
                        if (selectedSaat === slot.saat && !slot.dolu) {
                            button.classList.add('selected');
                            submitButton.disabled = false;
                        } else if (selectedSaat === slot.saat && slot.dolu) {
                            // Seçili saat doluysa butonu pasif yap ve kaydı sıfırla
                            selectedSaat = '';
                            saatInput.value = '';
                        }
                        
                        // Tıklama olayı (Sadece dolu olmayan butonlar için)
                        if (!slot.dolu) {
                             button.addEventListener('click', function() {
                                document.querySelectorAll('#saat_container button').forEach(btn => btn.classList.remove('selected'));
                                this.classList.add('selected');
                                saatInput.value = slot.saat;
                                submitButton.disabled = false; // Randevu alınabilir
                                selectedSaat = slot.saat;
                            });
                        }

                        saatContainer.appendChild(button);
                    });
                    
                    if (!anyAvailable) {
                         saatContainer.innerHTML = `<p class="mesaj-basarili">Tüm saatler doludur.</p>`;
                         submitButton.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('AJAX Hatası:', error);
                    saatContainer.innerHTML = `<p class="mesaj-hata">Saatleri çekerken bir sorun oluştu.</p>`;
                });
            }

            // --- C. DOKTOR SEÇİMİ DEĞİŞTİĞİNDE/SAYFA YÜKLENİRKEN ---
            
            // Doktor Seçimi Değiştiğinde
            doktorSelect.addEventListener('change', function() {
                const doktorId = this.value;
                tarihInput.value = '';
                saatInput.value = '';
                saatSecimiDiv.style.display = 'none';
                submitButton.disabled = true;
                selectedTarih = '';
                selectedSaat = '';

                if (doktorId) {
                    createTakvim(doktorId);
                } else {
                    tarihSecimiDiv.style.display = 'none';
                    takvimContainer.innerHTML = '';
                }
            });
            
            // Sayfa yüklenirken varsayılan seçili doktor varsa takvimi ve saati yükle
            if (doktorSelect.value) {
                createTakvim(doktorSelect.value);
                if (selectedTarih) {
                    saatSecimiDiv.style.display = 'block';
                    // İlk yüklemede, varsa varsayılan saati tekrar kontrol et
                    fetchSaatler(doktorSelect.value, selectedTarih); 
                }
            }
        });
    </script>
</body>
</html>