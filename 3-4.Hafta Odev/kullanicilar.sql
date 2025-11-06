
CREATE TABLE kullanicilar (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    kulad VARCHAR(50) NOT NULL UNIQUE,
    sifre VARCHAR(255) NOT NULL, -- HASHlenmiş şifre alanı
    rol VARCHAR(20) NOT NULL
);
-- '123456' şifresinin HASH'i (password_hash('123456', PASSWORD_DEFAULT) ile oluşturulmuş bir örnek)
INSERT INTO kullanicilar (kulad, sifre, rol) VALUES ('adminuser', '$2y$10$wT8K5F3Bf0F1J4G6H7L8U0R2Q3S4T5U6V7W8X9Y0Z1A2B3C4D5E6F7G8H9I0J1K2L3', 'admin');
INSERT INTO kullanicilar (kulad, sifre, rol) VALUES ('uyekullanici', '$2y$10$wT8K5F3Bf0F1J4G6H7L8U0R2Q3S4T5U6V7W8X9Y0Z1A2B3C4D5E6F7G8H9I0J1K2L3', 'uye');