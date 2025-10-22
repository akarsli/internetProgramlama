CREATE TABLE kullanicilar (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    kulad VARCHAR(50) NOT NULL UNIQUE,
    sifre VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL
);

--iki kullanıcının şifresi de '123456' şeklinde

INSERT INTO kullanicilar (kulad, sifre, rol) VALUES ('adminuser', '$2a$12$zysHyK2octEJUwF62dJaxeezAoYD9GGIuyya8LGYw95zu4QQB8.TK', 'admin');
INSERT INTO kullanicilar (kulad, sifre, rol) VALUES ('uyekullanici', '$2a$12$zysHyK2octEJUwF62dJaxeezAoYD9GGIuyya8LGYw95zu4QQB8.TK', 'uye');
