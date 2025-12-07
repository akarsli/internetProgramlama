<?php

class TıbbiKayit {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Doktor tarafından yeni bir tıbbi kayıt oluşturulmasını sağlar (CRUD: Create).
     * @param int $randevu_id Kaydın ait olduğu randevu ID'si
     * @param int $doktor_id Kaydı oluşturan doktorun Kullanici ID'si
     * @param int $hasta_id Kaydın ait olduğu hastanın Hastalar ID'si
     * @param string $not Muayene notları
     * @param string $teşhis Hastaya konulan teşhis
     * @param string $reçete Reçete bilgisi
     * @return bool İşlem başarılıysa true
     */
    public function kayitEkle($randevu_id, $doktor_id, $hasta_id, $not, $teşhis, $reçete) {
        $sql = "INSERT INTO Tıbbi_Kayitlar 
                (randevu_id, doktor_id, hasta_id, muayene_notu, teşhis, reçete_bilgisi) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$randevu_id, $doktor_id, $hasta_id, $not, $teşhis, $reçete]);
        } catch (\PDOException $e) {
            // Hata yakalama
            // echo "Kayıt ekleme hatası: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Belirli bir hastaya ait tüm tıbbi kayıtları listeler (Hasta Geçmişi).
     * Hastanın kendi kayıtlarına veya doktorun hastasının kayıtlarına erişimi için kullanılır.
     * @param int $hasta_id Hastanın Hastalar ID'si
     * @return array Kayıt listesi
     */
    public function hastaKayitlariniGetir($hasta_id) {
        $sql = "SELECT 
                    tk.*, 
                    k.ad AS doktor_ad, 
                    k.soyad AS doktor_soyad,
                    r.tarih_saat AS randevu_tarihi
                FROM 
                    Tıbbi_Kayitlar tk
                JOIN 
                    Kullanicilar k ON tk.doktor_id = k.kullanici_id
                JOIN
                    Randevular r ON tk.randevu_id = r.randevu_id
                WHERE 
                    tk.hasta_id = ?
                ORDER BY 
                    tk.kayit_tarihi DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$hasta_id]);
        return $stmt->fetchAll();
    }

    /**
     * Belirli bir randevu için daha önce tıbbi kayıt girilip girilmediğini kontrol eder.
     * @param int $randevu_id Kontrol edilecek randevu ID'si
     * @return array|false Kayıt varsa kaydı, yoksa false döndürür.
     */
    public function randevuKaydiVarMi($randevu_id) {
        $sql = "SELECT kayit_id FROM Tıbbi_Kayitlar WHERE randevu_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$randevu_id]);
        return $stmt->fetch();
    }

    /**
     * Tıbbi kaydı ID ile çeker. (Formu doldurmak için)
     */
    public function idIleKayitGetir($kayit_id) {
        $sql = "SELECT * FROM Tıbbi_Kayitlar WHERE kayit_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$kayit_id]);
        return $stmt->fetch();
    }
    
    /**
     * Mevcut bir tıbbi kaydı günceller (CRUD: Update).
     * @param int $kayit_id Güncellenecek kaydın ID'si
     * @param string $not
     * @param string $teşhis
     * @param string $reçete
     * @return bool İşlem başarılıysa true
     * Mevcut bir tıbbi kaydı günceller (CRUD: Update).
     * guncelleme_tarihi alanını otomatik olarak ayarlar.
     */
    public function kayitGuncelle($kayit_id, $not, $teşhis, $reçete) {
        $sql = "UPDATE Tıbbi_Kayitlar 
                SET muayene_notu = ?, teşhis = ?, reçete_bilgisi = ?, guncelleme_tarihi = NOW() 
                WHERE kayit_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$not, $teşhis, $reçete, $kayit_id]);
        } catch (\PDOException $e) {
            // echo "Kayıt güncelleme hatası: " . $e->getMessage();
            return false;
        }
    }
    
}
?>