# FİTNESS101 - Giriş Sistemi & Veritabanı Kurulumu

## 📋 İçindekiler
1. XAMPP Kurulumu
2. Veritabanı Kurulumu
3. Proje Yapılandırması
4. Test Hesapları
5. Giriş Sistemi Özellikleri

---

## 🚀 XAMPP Kurulumu

### 1. XAMPP İndirme
- https://www.apachefriends.org/ adresine git
- Windows için **XAMPP** indirme (en yeni versiyon)
- **xampp-windows-x64-versionnumber-installer.exe** dosyasını çalıştır

### 2. XAMPP Kurulumu
```
Kurulum yolunu seç: C:\xampp (varsayılan olarak iyi)
Apache seç ✓
MySQL seç ✓
PHP seç ✓
phpMyAdmin seç ✓
```

### 3. MySQL'i Başlat
1. **XAMPP Control Panel** aç
2. **MySQL** yanında "Start" butonuna tıkla
3. "Status: Running" göstermeli ✓
4. **Apache** yanında "Start" butonuna tıkla
5. "Status: Running" göstermeli ✓

---

## 📁 Proje Dosyalarını Doğru Yere Koy

SSFF-02 klasörünü şuraya taşı:
```
C:\xampp\htdocs\SSFF-02
```

Şimdi `http://localhost/SSFF-02/` adresinde açılabilecek

---

## 🗄️ Veritabanı Kurulumu

### Yöntem 1: phpMyAdmin (Kolay)

1. **phpMyAdmin Aç**
   - `http://localhost/phpmyadmin` ziyaret et
   - Kullanıcı: `root`
   - Şifre: (boş bırak)
   - Giriş yap ✓

2. **SQL Dosyasını İçeri Aktar**
   - Üst menüde "Import" tıkla
   - "Choose File" tıkla
   - `database.sql` dosyasını seç (SSFF-02 klasöründe)
   - "Go" butonuna tıkla ✓

3. **Kontrol Et**
   - Sol tarafta "fitness101" veritabanı görülmalı
   - Users, memberships vb tablolar içinde görülmeli

### Yöntem 2: Komut Satırı (İleri)

1. **CMD veya PowerShell Aç**
   
2. **MySQL'e Bağlan**
   ```bash
   cd C:\xampp\mysql\bin
   mysql -u root -p
   ```
   (Şifre sor tabısında Enter tuşuna bas)

3. **Veritabanı Oluştur**
   ```sql
   SOURCE C:/xampp/htdocs/SSFF-02/database.sql;
   SHOW DATABASES;
   ```

---

## 🔑 Test Hesapları

Veritabanına otomatik olarak eklenen test hesapları:

### Admin Hesabı
- **Kullanıcı Adı:** admin
- **Şifre:** admin123
- **Email:** admin@fitness101.com

### Kullanıcı Hesabı
- **Kullanıcı Adı:** user1
- **Şifre:** password123
- **Email:** user1@fitness101.com

---

## 🔗 Giriş Sistemi Dosyaları

| Dosya | Açıklama |
|-------|----------|
| `login.html` | Giriş sayfası |
| `login-process.php` | Giriş işlem dosyası |
| `register.html` | Kaydolma sayfası |
| `register-process.php` | Kaydolma işlem dosyası |
| `logout.php` | Çıkış işlemi |
| `session-check.php` | Session kontrol fonksiyonları |
| `check-admin-access.php` | Admin erişim kontrolü |
| `user-dashboard.html` | Kullanıcı paneli |
| `config.php` | Veritabanı konfigürasyonu |
| `database.sql` | Veritabanı kurulum scripti |

---

## 🧪 Test Etme

### 1. Ana Sayfa
1. `http://localhost/SSFF-02/` ziyaret et
2. Tüm sayfalar açılacak

### 2. Kaydolma Test
1. `http://localhost/SSFF-02/register.html` ziyaret et
2. Yeni hesap oluştur:
   - Kullanıcı Adı: testuser123
   - Email: test@example.com
   - Şifre: Test@1234
3. Başarıyla kaydolduysa login sayfasına yönlendir

### 3. Giriş Test
1. `http://localhost/SSFF-02/login.html` ziyaret et
2. Admin hesabıyla giriş:
   - Kullanıcı: admin
   - Şifre: admin123
3. Admin paneline erişebilmeli: `http://localhost/SSFF-02/admin.html`

### 4. Kullanıcı Paneli Test
1. Kullanıcı hesabıyla giriş: user1 / password123
2. `http://localhost/SSFF-02/user-dashboard.html` açılalı

### 5. Çıkış Test
1. `logout.php` linkine tıkla
2. Login sayfasına yönlendir

---

## 🛡️ Güvenlik Özellikleri

✅ SHA2-256 şifre hashleme
✅ SQL Injection koruması
✅ CSRF Token (config.php'de)
✅ Session zaman aşımı (30 dakika)
✅ Şifre gücü kontrolü
✅ Email doğrulama
✅ Admin erişim kontrolü
✅ Kullanıcı adı/Email duplikasyon kontrolü

---

## 🐛 Sorun Giderme

### Problem: "Veritabanı bağlantısı başarısız"
**Çözüm:**
1. MySQL'in çalışıp çalışmadığını kontrol et
2. XAMPP Control Panel'de MySQL'in yanında "Start" butonuna bas
3. config.php'deki ayarları kontrol et (DB_HOST, DB_USER, DB_PASS)

### Problem: "This page can't be found"
**Çözüm:**
1. Proje dosyasının `C:\xampp\htdocs\SSFF-02` içinde olduğunu kontrol et
2. `http://localhost/SSFF-02/index.html` adresini deneme

### Problem: "SQL syntax error"
**Çözüm:**
1. phpMyAdmin'den database.sql dosyasını dışa aktar-yeniden içeri aktar
2. SQL dosyasında hata olup olmadığını kontrol et
3. MySQL version compatibility'nini kontrol et

### Problem: "Session verisi silinmiş"
**Çözüm:**
1. config.php'deki session ayarlarını kontrol et
2. XAMPP `/tmp` klasörü boş mu kontrol et

---

## 📝 Sonraki Adımlar

1. **Email Doğrulama Sistemi** - register fonksiyonunda eklenebilir
2. **Şifre Sıfırlama** - forgot-password.html oluştur
3. **Two-Factor Authentication** - SMS/Email doğrulaması
4. **Ödeme Sistemi** - Stripe entegrasyonu
5. **Antrenman Kaydı** - Antrenman geçmiş takibi
6. **Raporlar** - PDF export fonksiyonları

---

## 📞 Destek

Sorun yaşanırsa:
1. Browser console'unda (F12) hata kontrolü
2. `error_reporting` XAMPP logs'unda bakma
3. phpMyAdmin'den tabloları kontrol etme

---

**Son Güncelleme:** Mart 4, 2026
**Versiyon:** 1.0
