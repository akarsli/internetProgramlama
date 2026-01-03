



MÜHENDİSLİK VE DOĞA BİLİMLERİ FAKÜLTESİ
BİLGİSAYAR MÜHENDİSLİĞİ BÖLÜMÜ
İNTERNET PROGRAMLAMA DERSİ FİNAL PROJESİ

Klinik Yönetim Sistemi



23120205070, Abdulkadir KARSLI



Ders Sorumlusu
Dr. Öğr. Üyesi Muhammet Sinan BAŞARSLAN 






Ocak, 2026
İstanbul Medeniyet Üniversitesi, İstanbul
  

İÇİNDEKİLER
Sayfa No
İÇİNDEKİLER	i
TABLO VE ŞEKİL LİSTESİ	ii
GİRİŞ	1
MATERYAL METOD	1
2.1. Önyüz Tasarım Araçları	1
2.2. Nesne İlişkisel Eşleme (ORM) ve Veritabanı	1
Klinik Sistemi	1
3.1. Önyüz Tasarımı ve Kullanıcı Deneyimi	1
3.2. Veritabanı ve Uygulama Arayüzleri	1
3.2.1.Hasta İşlem Paneli	3
3.2.2.Doktor İşlem Paneli	6
3.2.3.Admin İşlem Paneli	8

















TABLO VE ŞEKİL LİSTESİ
Şekil 1: Klinik Anasayfası	2
Şekil 2: Klinik Sistemine Giriş Sayfası	2
Şekil 3: Hasta Kayıt Sayfası	3
Şekil 4: Hasta İşlem Paneli	3
Şekil 5: Randevu Alma Sayfası	4
Şekil 6: Randevu Görüntüleme Sayfası	4
Şekil 7: Tıbbi Kayıt Görüntüleme Sayfası	5
Şekil 8: Hasta Bilgilerini Güncelleme Sayfası	5
Şekil 9: Doktor İşlem Paneli	6
Şekil 10: Randevu Takvimi Görüntüleme Sayfası	6
Şekil 11: Hasta Kayıtları Arama ve Görüntüleme Sayfası	7
Şekil 12: Doktor Bilgileri Düzenleme Sayfası	7
Şekil 13: Admin İşlem Paneli	8
Şekil 14: Admin Ekleme Sayfası 	    Şekil 15: Doktor Ekleme Sayfası	8
Şekil 16: Tüm Kullanıcıları Listeleme Sayfası	9
 

GİRİŞ
Günümüzde sağlık hizmetlerinin dijitalleşmesi, hem hasta memnuniyetini artırmak hem de klinik operasyonlarını optimize etmek adına bir zorunluluk haline gelmiştir. Yazılım geliştirme süreçlerinde, sistemin gereksinimlere uygunluğu ve hatasız çalışması büyük önem taşır. Geliştiricilerin kendi yazdıkları kodlardaki mantıksal hataları gözden kaçırma ihtimali, projenin son kullanıcıya ulaştığında ciddi aksaklıklara yol açmasına neden olabilir.
Bu projede, bir kliniğin ihtiyaç duyacağı randevu yönetimi, hasta takibi ve doktor çalışma planı gibi süreçler dijital ortama taşınmıştır. Yazılımın kalite güvencesi (QA) ilkeleri doğrultusunda, hataların süreç başında tespiti ve kullanıcı dostu bir arayüz sunulması hedeflenmiştir.

MATERYAL METOD
Projenin geliştirilmesinde modern web teknolojileri ve nesne tabanlı programlama prensipleri kullanılmıştır. Yazılım yaşam döngüsü boyunca test süreçlerine ağırlık verilmiş, veritabanı ve sunucu taraflı işlemler arasında tutarlılık sağlanmıştır.
2.1. Önyüz Tasarım Araçları
Uygulamanın arayüzü, farklı cihazlarda sorunsuz çalışabilmesi için duyarlı (responsive) bir yapıda kurgulanmıştır:
•	HTML & CSS: Sayfa yapısının oluşturulması ve klinik kurumsal kimliğine uygun görsel tasarım için kullanılmıştır.
•	JavaScript: Kullanıcı etkileşimleri (arama filtreleme, hamburger menü kontrolü, form doğrulamaları) için kullanılmıştır.
•	Responsive Tasarım: Mobil cihazlar için hamburger menü entegrasyonu ve esnek grid yapısı uygulanmıştır.
2.2. Nesne İlişkisel Eşleme (ORM) ve Veritabanı
Veri yönetimi için ilişkisel veritabanı (RDBMS) modeli tercih edilmiştir:
•	PHP & PDO: Veritabanı işlemlerinde güvenliği sağlamak (SQL Injection koruması) ve verileri nesne tabanlı bir yapıda işlemek için PHP Veri Nesneleri (PDO) kullanılmıştır.
•	Veri Tasarımı: Sistemde "Kullanıcılar", "Randevular", "Doktorlar" ve "Tıbbi Kayıtlar" tabloları arasında ilişkisel bir yapı kurulmuştur. 

Klinik Sistemi
Proje, üç temel kullanıcı rolü (Hasta, Doktor, Admin) üzerine inşa edilmiştir.
3.1. Önyüz Tasarımı ve Kullanıcı Deneyimi
Sistemin anasayfası; kliniğin uzmanlık alanlarını, doktor kadrosunu ve iletişim bilgilerini içeren kurumsal bir vitrin niteliğindedir.
•	Dinamik Menü: Giriş yapan kullanıcının rolüne göre (Hasta/Doktor/Admin) üst menü ve işlem butonları dinamik olarak değişmektedir.
•	Mobil Uyumluluk: Sayfa daraldığında menü içeriği hamburger ikonuna dönüşerek kullanım kolaylığı sağlar.
Alan	Kullanılan Teknoloji / Araç	Açıklama
Programlama Dili	PHP 8.x	Sunucu tarafından mantıksal işlemler ve veritabanı iletişimi için kullanılmıştır.
Veritabanı Sistemi	MySQL (İlişkisel DB)	Kullanıcı, randevu ve tıbbi kayıt verilerinin ACID prensiplerine uygun depolanmasını sağlar. 
Önyüz	HTML5 & CSS3	Sayfa yapısının iskeleti ve görsel tasarımı bu teknolojilerle oluşturulmuştur.
Etkileşim (Script)	JavaScript(ES6+)	İstemci tarafında çalışan arama filtreleme ve hamburger menü kontrollerini sağlar.
Veri Bağlantısı	PHP Veri Nesneleri	Veritabanı katmanında güvenli (SQL Injection korumalı) veri erişim sağlayan bir ORM tekniğidir. 
Tasarım Yaklaşımı	Responsive	Bootstrap/Tailwind benzeri yaklaşımlarla mobil ve masaüstü cihazlara uyumluluk sağlanmıştır. 
Tablo 1:Projede Kullanılan Teknolojiler
3.2. Veritabanı ve Uygulama Arayüzleri
•	Admin Paneli: Kullanıcıları listeleme, arama (filtreleme), düzenleme, pasifleştirme ve kalıcı olarak silme fonksiyonlarını içerir.
•	Doktor Paneli: Doktorun bugünkü randevu sayısını ve tıbbi kaydı eksik olan hastalarını özetleyen dinamik bir dashboard sunar.
•	Hasta Paneli: Uzmanlık alanına göre doktor seçimi ve randevu alma süreçlerini yönetir.
 
Şekil 1: Klinik Anasayfası
Şekil 1’de görüldüğü üzere hakkımızda, hizmetler, doktorlar, iletişim, giriş yap ve kayıt ol sayfalarına gidilebilir.

 
Şekil 2: Klinik Sistemine Giriş Sayfası
Şekil 2’de görüldüğü üzere kullanıcı (admin, hasta veya doktor) bu sayfadan siteye giriş yapacaktır. Hangi role sahipse buna uygun kullanıcı paneline gidecektir. Eğer kayıt olmadıysa “kayıt olmadınız mı?” linkine tıklayarak kayıt sayfasına gidebilir. 
 
Şekil 3: Hasta Kayıt Sayfası
Şekil 3’de görüldüğü üzere kayıt olmayan hastalar bu sayfadan kayıt olabilirler.
3.2.1.Hasta İşlem Paneli

 
Şekil 4: Hasta İşlem Paneli
    Şekil 4’de görüldüğü üzere ana sayfa, yeni randevu al, mevcut randevuları görüntüle, tıbbi kayıtlarımı görüntüle, kişisel bilgileri düzenle ve çıkış yap butonları yer almaktadır. Hasta bu butonlara tıklayarak ilgili sayfalara gidebilir.



Şekil 5: Randevu Alma Sayfası
Şekil 5’de görüldüğü üzere hasta istediği doktoru ve önümüzdeki 7 gün içinden istediği bir saati seçerek istediği randevuyu oluşturabilir.

 
Şekil 6: Randevu Görüntüleme Sayfası
    Şekil 6’da görüldüğü üzere bu sayfada mevcut ve geçmiş randevuları görebilir ve iptal etme işlemi yapılabilir.

 
Şekil 7: Tıbbi Kayıt Görüntüleme Sayfası
    Şekil 7’de görüldüğü üzere randevu sonrası doktorun yazdığı tıbbi kaydı hasta kontrol edebilir.

 
   Şekil 8: Hasta Bilgilerini Güncelleme Sayfası
    Şekil 8’de görüldüğü üzere hasta bilgilerini ve şifresini güncelleyebilir.

3.2.2.Doktor İşlem Paneli

 
Şekil 9: Doktor İşlem Paneli
    Şekil 9’da görüldüğü üzere ana sayfa, randevularım, hasta kayıtları arama ve oluşturma, kişisel bilgileri düzenle ve çıkış yap butonları yer almaktadır. Doktor bu butonlara tıklayarak  ilgili sayfalara gidebilir.

 
Şekil 10: Randevu Takvimi Görüntüleme Sayfası
    Şekil 10’da görüldüğü üzere doktor, randevu takvimi üzerinden hastanın talep ettiği randevuyu kabul edebilir/reddedebilir. Kabul ettikten sonra randevuyu tamamlayıp tıbbi kayıt ekleyebilir. Tıbbi kayıt ekledikten sonra düzenleyebilir.

 
          Şekil 11: Hasta Kayıtları Arama ve Görüntüleme Sayfası
    Şekil 11’de görüldüğü üzere doktor bir hastasının mail adresini girdikten sonra o hastaya ait tıbbi kayıtları inceleyip düzenleyebilir.
 
Şekil 12: Doktor Bilgileri Düzenleme Sayfası
    Şekil 12’de görüldüğü üzere doktor bilgilerini ve şifresini güncelleyebilir.
3.2.3.Admin İşlem Paneli

 
Şekil 13: Admin İşlem Paneli
Şekil 13’de görüldüğü üzere ana sayfa, admin ekle, doktor ekle, tüm kullanıcıları listele ve çıkış yap butonları yer almaktadır. Admin bu butonlara tıklayarak ilgili sayfalara gidebilir.

        
   Şekil 14: Admin Ekleme Sayfası 	    Şekil 15: Doktor Ekleme Sayfası
    Şekil 14 ve 15’de görüldüğü üzere admin doktor ve admin ekleyebilir.

 
Şekil 16: Tüm Kullanıcıları Listeleme Sayfası
    Şekil 16’da görüldüğü üzere bu sayfada admin kullanıcılara ait işlemler yapabilmektedir. Bunlar; kullanıcı bilgilerini güncelleme, kullanıcıyı pasifleştirme ve kullanıcıyı silme.


1.	SONUÇ VE TARTIŞMA
Proje geliştirme sürecinde, yerel bir sunucuda (localhost) geliştirme yapmanın avantajları ve canlı sistemlerde karşılaşılabilecek veritabanı kısıtlamaları (Foreign Key hataları vb.) deneyimlenmiştir. Özellikle admin panelindeki arama fonksiyonunun JavaScript ile istemci tarafında yapılmasının hızı artırdığı, ancak büyük veri setlerinde sunucu taraflı sorguların gerekliliği tartışılmıştır. Gelecekte sisteme mobil bildirimler (push notifications) ve online ödeme sistemlerinin entegre edilmesi planlanmaktadır.  
2.	EKLER
Projenin GitHub linki: https://github.com/akarsli/internetProgramlama/tree/main/finalProject

























