<?php
// Bu dosya, Randevu ve Klinik İşlemleri için CRUD metodlarını içerir.

class Randevu {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Hastanın yeni bir randevu oluşturmasını sağlar (CRUD: Create).
     * @param int $doktor_id Seçilen doktorun ID'si (Kullanicilar.kullanici_id)
     * @param int $hasta_id Hastanın ID'si (Hastalar.hasta_id)
     * @param string $tarih_saat Randevu tarihi ve saati (YYYY-MM-DD HH:MM:SS formatında)
     * @return bool İşlem başarılıysa true, değilse false
     */
    public function randevuOlustur($doktor_id, $hasta_id, $tarih_saat) {
        $sql = "INSERT INTO Randevular (doktor_id, hasta_id, tarih_saat, durum) 
                VALUES (?, ?, ?, 'Planlandı')";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$doktor_id, $hasta_id, $tarih_saat]);
        } catch (\PDOException $e) {
            // Hata yakalama
            // echo "Randevu oluşturma hatası: " . $e->getMessage(); 
            return false;
        }
    }

    /**
     * Belirli bir hastanın randevularını listeler.
     * @param int $hasta_id Hastanın Hastalar tablosundaki ID'si
     * @return array Randevu listesi
     */
    public function hastaRandevulariniGetir($hasta_id) {
        $sql = "SELECT 
                    r.randevu_id,
                    r.tarih_saat,
                    r.durum,
                    k.ad AS doktor_ad,
                    k.soyad AS doktor_soyad
                FROM 
                    Randevular r
                JOIN 
                    Kullanicilar k ON r.doktor_id = k.kullanici_id
                WHERE 
                    r.hasta_id = ?
                ORDER BY 
                    r.tarih_saat DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$hasta_id]);
        return $stmt->fetchAll();
    }

    /**
     * Belirli bir doktorun randevularını listeler. (ÇÖZÜM İÇİN GÜNCELLENDİ)
     * @param int $doktor_id Doktorun Kullanici ID'si
     * @return array Randevu listesi
     */
    public function doktorRandevulariniGetir($doktor_id) {
        $sql = "SELECT 
                    r.randevu_id,
                    r.tarih_saat,
                    r.durum,
                    u.ad AS hasta_ad,
                    u.soyad AS hasta_soyad
                FROM 
                    Randevular r
                JOIN 
                    Hastalar h ON r.hasta_id = h.hasta_id   /* 1. Randevuyu Hastalar tablosuna bağla */
                JOIN 
                    Kullanicilar u ON h.kullanici_id = u.kullanici_id /* 2. Hastayı Kullanicilar tablosuna (ad/soyad için) bağla */
                WHERE 
                    r.doktor_id = ?
                ORDER BY 
                    r.tarih_saat ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$doktor_id]);
        return $stmt->fetchAll();
    }

    /**
     * Hastanın veya doktorun randevuyu iptal etmesini sağlar (CRUD: Delete).
     * @param int $randevu_id İptal edilecek randevunun ID'si
     * @return bool İşlem başarılıysa true
     */
    public function randevuIptalEt($randevu_id) {
        // Durumu 'İptal Edildi' olarak güncellemek, silmekten daha güvenli bir yaklaşımdır.
        $sql = "UPDATE Randevular SET durum = 'İptal Edildi' WHERE randevu_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$randevu_id]);
        } catch (\PDOException $e) {
            // echo "Randevu iptal hatası: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Doktorun randevu durumunu (Onaylandı/Tamamlandı) güncellemesini sağlar (CRUD: Update).
     * @param int $randevu_id Güncellenecek randevunun ID'si
     * @param string $yeni_durum Yeni durum (Örn: 'Onaylandı', 'Tamamlandı', 'Reddedildi')
     * @return bool İşlem başarılıysa true
     */
    public function randevuDurumGuncelle($randevu_id, $yeni_durum) {
        $sql = "UPDATE Randevular SET durum = ? WHERE randevu_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$yeni_durum, $randevu_id]);
        } catch (\PDOException $e) {
            // echo "Randevu durum güncelleme hatası: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Randevuyu veritabanından kalıcı olarak siler (Önce Tıbbi Kayıtları kontrol etmeli).
     * @param int $randevu_id Silinecek randevunun ID'si
     * @return bool İşlem başarılıysa true
     */
    public function randevuKalıcıSil($randevu_id) {
        // Önce bağlı tıbbi kaydı silmek zorundayız (Foreign Key Kısıtlaması nedeniyle)
        $sql_kayit_sil = "DELETE FROM Tıbbi_Kayitlar WHERE randevu_id = ?";
        $stmt_kayit = $this->pdo->prepare($sql_kayit_sil);
        $stmt_kayit->execute([$randevu_id]);

        // Ardından randevuyu sil
        $sql_randevu_sil = "DELETE FROM Randevular WHERE randevu_id = ?";
        
        try {
            $stmt_randevu = $this->pdo->prepare($sql_randevu_sil);
            return $stmt_randevu->execute([$randevu_id]);
        } catch (\PDOException $e) {
            // echo "Kalıcı silme hatası: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Belirli bir doktorun belirli bir tarihteki dolu saatlerini çeker.
     * @param int $doktor_id Doktorun Kullanici ID'si
     * @param string $tarih Kontrol edilecek tarih (YYYY-MM-DD)
     * @return array Dolu saatlerin listesi (Örn: ['10:00:00', '14:30:00'])
     */
    public function doluSaatleriGetir($doktor_id, $tarih) {
        // Sadece 'Planlandı' ve 'Onaylandı' durumundaki randevuları dolu kabul ediyoruz.
        // Tarih başlangıcı (00:00:00) ve bitişi (23:59:59) arasındaki randevuları çeker.
        $sql = "SELECT 
                    TIME(tarih_saat) AS saat 
                FROM 
                    Randevular
                WHERE 
                    doktor_id = ? AND 
                    DATE(tarih_saat) = ? AND 
                    durum IN ('Planlandı', 'Onaylandı')"; 
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$doktor_id, $tarih]);
        
        // Sadece saat değerlerini içeren tek boyutlu bir dizi döndür
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Belirli bir doktorun bugünkü (Planlandı/Onaylandı) randevu sayısını verir.
     * @param int $doktor_id Doktorun Kullanici ID'si
     * @return int Randevu sayısı
     */
    public function bugunkuRandevuSayisiniGetir($doktor_id) {
        $bugun = date('Y-m-d');
        $sql = "SELECT COUNT(randevu_id) 
                FROM Randevular 
                WHERE doktor_id = ? AND 
                      DATE(tarih_saat) = ? AND 
                      durum IN ('Planlandı', 'Onaylandı')";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$doktor_id, $bugun]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Doktorun 'Tamamlandı' olarak işaretlediği ancak henüz Tıbbi Kayıt girilmemiş randevu sayısını verir.
     * @param int $doktor_id Doktorun Kullanici ID'si
     * @return int Kayıt eksikliği olan randevu sayısı
     */
    public function eksikKayitSayisiniGetir($doktor_id) {
        $sql = "SELECT COUNT(r.randevu_id)
                FROM Randevular r
                LEFT JOIN Tıbbi_Kayitlar tk ON r.randevu_id = tk.randevu_id
                WHERE r.doktor_id = ? 
                  AND r.durum = 'Tamamlandı' 
                  AND tk.randevu_id IS NULL"; // Tıbbi kaydı olmayanları bul
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$doktor_id]);
        return (int)$stmt->fetchColumn();
    }
}
?>