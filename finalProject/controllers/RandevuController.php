<?php
// Kontrolcü, mantık işleme ve model ile view arasında köprü kurar.

require_once __DIR__ . '/../config/db_baglanti.php';
require_once __DIR__ . '/../models/Randevu.php';
require_once __DIR__ . '/../models/Kullanici.php'; // Doktor listesi için

class RandevuController {
    private $pdo;
    private $randevuModel;
    private $kullaniciModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->randevuModel = new Randevu($pdo);
        $this->kullaniciModel = new Kullanici($pdo);
    }

    /**
     * Randevu oluşturma işlemini yönetir (Hasta rolü için).
     * @param array $postData POST ile gelen form verileri
     * @param int $hastaKullaniciId Oturumdaki hastanın kullanici_id'si
     */
    public function olustur($postData, $hastaKullaniciId) {
        $doktor_id = $postData['doktor_id'] ?? null;
        $tarih = $postData['tarih'] ?? null;
        $saat = $postData['saat'] ?? null;
        $tarih_saat = $tarih . ' ' . $saat . ':00';
        $hata = '';

        if (empty($doktor_id) || empty($tarih) || empty($saat)) {
            $hata = "Lütfen tüm alanları doldurun.";
        } elseif (strtotime($tarih_saat) < time()) {
            $hata = "Geçmiş bir tarihe randevu alamazsınız.";
        } else {
            // KRİTİK: Kullanici ID'den Hastalar tablosundaki doğru hasta_id'yi çekme
            $sql_get_hasta_id = "SELECT hasta_id FROM Hastalar WHERE kullanici_id = ?";
            $stmt_hasta_id = $this->pdo->prepare($sql_get_hasta_id);
            $stmt_hasta_id->execute([$hastaKullaniciId]);
            $hasta_kaydi = $stmt_hasta_id->fetch();

            if (!$hasta_kaydi) {
                 $hata = "Hata: Hasta detay kaydınız bulunamadı.";
            } else {
                $hasta_id = $hasta_kaydi['hasta_id'];
                
                if ($this->randevuModel->randevuOlustur($doktor_id, $hasta_id, $tarih_saat)) {
                    $mesaj = "Randevunuz başarıyla oluşturuldu!";
                    // Başarılıysa view'a mesajı iletmek için geri dönebilir
                    return ['mesaj' => $mesaj, 'basarili' => true];
                } else {
                    $hata = "Randevu oluşturulurken bir hata oluştu. Lütfen tekrar deneyin.";
                }
            }
        }
        return ['hata' => $hata, 'basarili' => false];
    }
    
    /**
     * Randevu iptal işlemini yönetir.
     * @param int $randevuId İptal edilecek randevunun ID'si
     * @return array Sonuç
     */
    public function iptalEt($randevuId) {
        if ($this->randevuModel->randevuIptalEt($randevuId)) {
            $mesaj = "Randevunuz başarıyla iptal edilmiştir.";
            return ['mesaj' => $mesaj, 'basarili' => true];
        } else {
            $hata = "Randevu iptal edilirken bir hata oluştu.";
            return ['hata' => $hata, 'basarili' => false];
        }
    }
    
    /**
     * Randevu durumu güncelleme işlemini yönetir (Doktor rolü için).
     * @param int $randevuId Güncellenecek randevu ID'si
     * @param string $yeniDurum Yeni durum ('Onaylandı', 'Tamamlandı', 'Reddedildi')
     * @return array Sonuç
     */
    public function durumGuncelle($randevuId, $yeniDurum) {
        $gecerli_durumlar = ['Onaylandı', 'Tamamlandı', 'Reddedildi'];
        if (!in_array($yeniDurum, $gecerli_durumlar)) {
            return ['hata' => 'Geçersiz durum parametresi.', 'basarili' => false];
        }

        if ($this->randevuModel->randevuDurumGuncelle($randevuId, $yeniDurum)) {
            $mesaj = "Randevu durumu başarıyla **{$yeniDurum}** olarak güncellendi.";
            return ['mesaj' => $mesaj, 'basarili' => true];
        } else {
            $hata = "Durum güncellenirken bir hata oluştu.";
            return ['hata' => $hata, 'basarili' => false];
        }
    }
}
?>