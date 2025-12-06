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
    public function kayitOl($ad, $soyad, $e_posta, $sifre, $rol_id) {
        $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);

        $sql = "INSERT INTO Kullanicilar (ad, soyad, e_posta, sifre_hash, rol_id, aktif_mi) 
                VALUES (?, ?, ?, ?, ?, 1)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$ad, $soyad, $e_posta, $hashed_sifre, $rol_id])) {
                // Başarılı olursa, eklenen kaydın ID'sini döndür
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (\PDOException $e) {
            // echo "Hata: " . $e->getMessage(); 
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
        $sql = "SELECT k.kullanici_id, k.ad, k.soyad, k.e_posta, k.telefon, k.aktif_mi, r.rol_adi 
                FROM Kullanicilar k
                JOIN Roller r ON k.rol_id = r.rol_id
                ORDER BY r.rol_adi, k.soyad";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function doktorlariGetir() {
        // Roller tablosunda Doktor rolünün ID'sinin 2 olduğunu varsayıyoruz.
        // Güvenlik için rol_adi ile sorgulama daha iyidir.
        $sql = "SELECT k.kullanici_id, k.ad, k.soyad 
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
        $sql = "SELECT k.ad, k.soyad, k.e_posta, k.telefon, r.rol_adi 
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
    public function adminKullaniciGuncelle($kullanici_id, $ad, $soyad, $e_posta, $telefon, $rol_id) {
        $sql = "UPDATE Kullanicilar 
                SET ad = ?, soyad = ?, e_posta = ?, telefon = ?, rol_id = ? 
                WHERE kullanici_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$ad, $soyad, $e_posta, $telefon, $rol_id, $kullanici_id]);
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
    
}
?>