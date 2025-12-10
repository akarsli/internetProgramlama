<?php
// Bağlantı nesnesini (pdo) dışarıdan alıyoruz
require_once __DIR__ . '/../config/db_baglanti.php';

class Kullanici {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Kullanıcıyı e-posta ve şifre ile doğrular.
     * @param string $e_posta
     * @param string $sifre
     * @return array|false Doğrulanmış kullanıcı verilerini veya false döndürür.
     */
    public function girisYap($e_posta, $sifre) {
        $sql = "SELECT k.*, r.rol_adi 
                FROM Kullanicilar k
                JOIN Roller r ON k.rol_id = r.rol_id
                WHERE k.e_posta = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$e_posta]);
        $kullanici = $stmt->fetch();

        if ($kullanici && password_verify($sifre, $kullanici['sifre_hash'])) {
            // Şifre doğru, kullanıcı bilgilerini döndür
            return $kullanici;
        }
        
        // Kullanıcı bulunamadı veya şifre yanlış
        return false;
    }

    /**
     * Yeni bir kullanıcı kaydı yapar (Örn: Admin, Hasta kaydı)
     * @param string $ad
     * @param string $soyad
     * @param string $e_posta
     * @param string $sifre
     * @param int $rol_id
     * @return bool İşlem başarılıysa true, değilse false
     */
    public function kayitOl($ad, $soyad, $e_posta, $sifre, $rol_id, $uzmanlik_alani = null) {
    
    // Uzmanlık alanı parametresi eklendi, varsayılan değeri null olarak ayarlandı
    
    $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);

    // SQL sorgusu bu parametreyi kullanacak şekilde zaten güncellenmişti:
    $sql = "INSERT INTO Kullanicilar (ad, soyad, e_posta, sifre_hash, rol_id, uzmanlik_alani, aktif_mi) 
            VALUES (?, ?, ?, ?, ?, ?, 1)";
    
    try {
        $stmt = $this->pdo->prepare($sql);
        // Parametreler listesine $uzmanlik_alani eklendi
        if ($stmt->execute([$ad, $soyad, $e_posta, $hashed_sifre, $rol_id, $uzmanlik_alani])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    } catch (\PDOException $e) {
        // ...
        return false;
    }
}

    /**
     * Hastalar tablosuna yeni kaydı ekler.
     * @param int $kullanici_id Yeni eklenen kullanıcının ID'si
     * @return bool İşlem başarılıysa true
     */
    public function hastaDetayEkle($kullanici_id) {
        // Not: Şimdilik sadece zorunlu olan kullanici_id'yi ekliyoruz.
        // Daha sonra formdan TC, doğum tarihi vb. bilgileri alıp ekleyebilirsiniz.
        $sql = "INSERT INTO Hastalar (kullanici_id) VALUES (?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$kullanici_id]);
        } catch (\PDOException $e) {
            // echo "Hasta detay kaydı hatası: " . $e->getMessage();
            return false;
        }
    }
    
    // Temel CRUD: Tek bir kullanıcıyı ID ile Getir (Read)
    public function tumKullanicilariGetir() {
        $sql = "SELECT k.kullanici_id, k.ad, k.soyad, k.e_posta, k.telefon, k.uzmanlik_alani, k.aktif_mi, r.rol_adi
                FROM Kullanicilar k
                JOIN Roller r ON k.rol_id = r.rol_id
                ORDER BY k.rol_id, k.soyad";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function doktorlariGetir() {
        $sql = "SELECT k.kullanici_id, k.ad, k.soyad, k.uzmanlik_alani, r.rol_adi 
                FROM Kullanicilar k
                JOIN Roller r ON k.rol_id = r.rol_id
                WHERE r.rol_adi = 'Doktor' AND k.aktif_mi = 1
                ORDER BY k.soyad";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * ID ile tek bir kullanıcının bilgilerini getirir.
     * @param int $kullanici_id
     * @return array|false Kullanıcı verilerini veya false döndürür.
     */
    public function idIleKullaniciGetir($kullanici_id) {
        // Uzmanlık alanı çekiliyor
        $sql = "SELECT k.ad, k.soyad, k.e_posta, k.telefon, k.rol_id, k.uzmanlik_alani, r.rol_adi 
                FROM Kullanicilar k 
                JOIN Roller r ON k.rol_id = r.rol_id
                WHERE k.kullanici_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$kullanici_id]);
        return $stmt->fetch();
    }

    /**
     * Kullanıcının temel bilgilerini günceller (CRUD: Update).
     * @param int $kullanici_id Güncellenecek kullanıcının ID'si
     * @param string $ad
     * @param string $soyad
     * @param string $e_posta
     * @param string $telefon
     * @return bool İşlem başarılıysa true
     */
    public function kullaniciBilgiGuncelle($kullanici_id, $ad, $soyad, $e_posta, $telefon) {
        $sql = "UPDATE Kullanicilar 
                SET ad = ?, soyad = ?, e_posta = ?, telefon = ? 
                WHERE kullanici_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$ad, $soyad, $e_posta, $telefon, $kullanici_id]);
        } catch (\PDOException $e) {
            // E-posta benzersizlik hatası vb. yakalanabilir
            // echo "Güncelleme hatası: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Kullanıcının şifresini günceller (Opsiyonel, ayrı bir formda olmalıdır).
     * @param int $kullanici_id
     * @param string $yeni_sifre
     * @return bool
     */
    public function sifreGuncelle($kullanici_id, $yeni_sifre) {
        $hashed_sifre = password_hash($yeni_sifre, PASSWORD_DEFAULT);
        $sql = "UPDATE Kullanicilar SET sifre_hash = ? WHERE kullanici_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$hashed_sifre, $kullanici_id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Adminin bir kullanıcının temel bilgilerini ve rolünü günceller (CRUD: Update).
     * @param int $kullanici_id Güncellenecek kullanıcının ID'si
     * @param string $ad
     * @param string $soyad
     * @param string $e_posta
     * @param string $telefon
     * @param int $rol_id Yeni rolün ID'si
     * @return bool İşlem başarılıysa true
     */
    public function adminKullaniciGuncelle($kullanici_id, $ad, $soyad, $e_posta, $telefon, $rol_id, $uzmanlik_alani) {
        // Uzmanlık alanı eklendi
        $sql = "UPDATE Kullanicilar 
                SET ad = ?, soyad = ?, e_posta = ?, telefon = ?, rol_id = ?, uzmanlik_alani = ? 
                WHERE kullanici_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            // Uzmanlık alanı parametresi eklendi
            return $stmt->execute([$ad, $soyad, $e_posta, $telefon, $rol_id, $uzmanlik_alani, $kullanici_id]);
        } catch (\PDOException $e) {
            // echo "Admin Güncelleme hatası: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Kullanıcı hesabını pasifleştirir (CRUD: Delete/Update).
     * @param int $kullanici_id Pasifleştirilecek kullanıcının ID'si
     * @return bool İşlem başarılıysa true
     */
    public function kullaniciPasiflestir($kullanici_id) {
        // Silmek yerine aktif_mi = 0 yapıyoruz.
        $sql = "UPDATE Kullanicilar SET aktif_mi = 0 WHERE kullanici_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$kullanici_id]);
        } catch (\PDOException $e) {
            // echo "Pasifleştirme hatası: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Kullanıcı hesabını tekrar aktif hale getirir (CRUD: Update).
     * @param int $kullanici_id Aktif edilecek kullanıcının ID'si
     * @return bool İşlem başarılıysa true
     */
    public function kullaniciAktiflestir($kullanici_id) {
        // aktif_mi = 1 yaparak hesabı aktif hale getirir.
        $sql = "UPDATE Kullanicilar SET aktif_mi = 1 WHERE kullanici_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$kullanici_id]);
        } catch (\PDOException $e) {
            // echo "Aktifleştirme hatası: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Tüm rolleri ID ve Ad olarak çeker (Update formu için gereklidir).
     * @return array Rol listesi
     */
    public function tumRolleriGetir() {
        $sql = "SELECT rol_id, rol_adi FROM Roller ORDER BY rol_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Sistemdeki tüm benzersiz uzmanlık alanlarını (aktif hizmetleri) çeker.
     * @return array Benzersiz uzmanlık alanlarının listesi
     */
    public function aktifHizmetleriGetir() {
        // DISTINCT kullanarak uzmanlık alanlarını tekrarsız ve boş olmayanları çeker
        $sql = "SELECT DISTINCT uzmanlik_alani 
                FROM Kullanicilar 
                WHERE rol_id = 2 AND aktif_mi = 1 AND uzmanlik_alani IS NOT NULL AND uzmanlik_alani != ''
                ORDER BY uzmanlik_alani";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        // Sadece tek boyutlu bir alan listesi döndürmek için kullanışlıdır
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Bir kullanıcıyı veritabanından kalıcı olarak siler (CRUD: Delete).
     * Foreign Key kısıtlamaları nedeniyle ilişkili kayıtları silmeye çalışır.
     * @param int $kullanici_id Silinecek kullanıcının ID'si
     * @return bool İşlem başarılıysa true
     */
    public function kullaniciKalıcıSil($kullanici_id, $rol_adi) {
        try {
            // Önemli: Foreign Key kısıtlamalarını geçici olarak kapatmak gerekebilir.
            // set foreign_key_checks = 0;

            // 1. Tıbbi Kayıtları Sil (Doktor veya Hastanın kayıtları)
            if ($rol_adi === 'Doktor') {
                $sql = "DELETE FROM Tıbbi_Kayitlar WHERE doktor_id = ?";
                $this->pdo->prepare($sql)->execute([$kullanici_id]);
            }

            // 2. Randevuları Sil (Doktor veya Hastanın randevuları)
            // Not: Hastanın randevuları için hasta_id'yi bulmak gerekir. Basitleştirelim.
            $sql_randevu = "DELETE FROM Randevular WHERE doktor_id = ? OR hasta_id IN (SELECT hasta_id FROM Hastalar WHERE kullanici_id = ?)";
            $this->pdo->prepare($sql_randevu)->execute([$kullanici_id, $kullanici_id]);

            // 3. Hastalar/Doktorlar Detaylarını Sil
            if ($rol_adi === 'Hasta') {
                $sql = "DELETE FROM Hastalar WHERE kullanici_id = ?";
                $this->pdo->prepare($sql)->execute([$kullanici_id]);
            } elseif ($rol_adi === 'Doktor') {
                 // Doktor detay tablosu yoksa bu adımı atlarız.
            }
            // Not: Admin rolü için özel bir detay tablosu yok.
            
            // 4. Ana Kullanıcı Kaydını Sil
            $sql_ana = "DELETE FROM Kullanicilar WHERE kullanici_id = ?";
            $stmt = $this->pdo->prepare($sql_ana);
            return $stmt->execute([$kullanici_id]);
            
        } catch (\PDOException $e) {
            // echo "Kalıcı silme hatası: " . $e->getMessage();
            return false;
        }
    }

}
?>