# FİTNESS101 — GitHub & cPanel Deploy Rehberi

Bu döküman projeyi GitHub'a nasıl yükleyeceğini, cPanel'e Git ile nasıl çekeceğini
ve veritabanını nasıl aktaracağını adım adım anlatır. Her deploy'da tekrar kullanabilirsin.

---

## BÖLÜM 1: GitHub'a İlk Push

### 1.1 — Git Kur (Sadece İlk Sefer)

Bilgisayarında Git yoksa: https://git-scm.com/downloads adresinden indir ve kur.

Kurulumu doğrula:
```
git --version
```

### 1.2 — GitHub'da Repo Oluştur

1. https://github.com adresine git, giriş yap
2. Sağ üstteki **+** butonuna tıkla → **New repository**
3. Repository name: `SSFF-02` (veya istediğin isim)
4. **Private** seç (projen gizli kalır)
5. "Add a README file" işaretleME (zaten var)
6. **Create repository** tıkla
7. Açılan sayfadaki HTTPS URL'ini kopyala:
   ```
   https://github.com/KULLANICI_ADIN/SSFF-02.git
   ```

### 1.3 — Lokalde Git Başlat ve Push Et

Terminal veya PowerShell'i aç, proje klasörüne git:

```powershell
cd C:\xampp\htdocs\SSFF-02
```

Sırasıyla bu komutları çalıştır:

```powershell
# Git başlat
git init

# Tüm dosyaları staging'e ekle
git add .

# İlk commit
git commit -m "İlk commit: FİTNESS101 projesi"

# Ana branch'i main olarak ayarla
git branch -M main

# GitHub remote'unu ekle (URL'ini kendi repo URL'inle değiştir)
git remote add origin https://github.com/KULLANICI_ADIN/SSFF-02.git

# Push et
git push -u origin main
```

> **Not:** İlk push'ta GitHub kullanıcı adı ve şifre/token isteyecek.
> GitHub artık şifre kabul etmiyor, **Personal Access Token** kullanman gerekiyor.

### 1.4 — GitHub Personal Access Token Oluştur

1. GitHub'da sağ üst profil fotoğrafı → **Settings**
2. Sol menüde en altta **Developer settings**
3. **Personal access tokens** → **Tokens (classic)**
4. **Generate new token (classic)** tıkla
5. Note: `cpanel-deploy` yaz
6. Expiration: **No expiration** (veya istediğin süre)
7. Scope: **repo** kutusunu işaretle (tüm alt kutular otomatik seçilir)
8. **Generate token** tıkla
9. Çıkan token'ı HEMEN kopyala (bir daha göremezsin!)
10. Token'ı güvenli bir yere kaydet

Push sırasında şifre sorulduğunda **bu token'ı yapıştır**.

---

## BÖLÜM 2: Sonraki Push'lar (Her Değişiklikte)

Projede değişiklik yaptıktan sonra:

```powershell
cd C:\xampp\htdocs\SSFF-02

# Değişiklikleri gör
git status

# Tüm değişiklikleri ekle
git add .

# Commit at (mesajı değiştir)
git commit -m "Kullanıcı paneli güncellendi"

# GitHub'a gönder
git push
```

> **Kısayol:** Her seferinde sadece bu 3 komutu çalıştırman yeterli:
> `git add .` → `git commit -m "mesaj"` → `git push`

---

## BÖLÜM 3: cPanel'de Git Version Control ile Çekme

### 3.1 — cPanel'e Giriş

1. Hosting sağlayıcından aldığın cPanel adresine git:
   ```
   https://seninsiten.com:2083
   ```
   veya hosting panelinden "cPanel" butonuna tıkla

### 3.2 — Git Version Control Aç

1. cPanel ana sayfasında arama kutusuna **"Git"** yaz
2. **Git™ Version Control** tıkla

### 3.3 — Repository Oluştur (Sadece İlk Sefer)

1. **Create** butonuna tıkla
2. Ayarları doldur:
   - **Clone URL:** `https://github.com/KULLANICI_ADIN/SSFF-02.git`
   - **Repository Path:** `/home/CPANEL_KULLANICI/public_html/SSFF-02`
     (veya `/home/CPANEL_KULLANICI/public_html` eğer ana domain'e kuracaksan)
   - **Repository Name:** `SSFF-02`
3. **Create** tıkla

> **Private Repo İçin:** Clone URL'ine token eklemen gerekir:
> ```
> https://GITHUB_KULLANICI:TOKEN@github.com/KULLANICI_ADIN/SSFF-02.git
> ```
> Örnek:
> ```
> https://ahmet:ghp_abc123xyz@github.com/ahmet/SSFF-02.git
> ```

### 3.4 — Güncellemeleri Çekme (Her Deploy'da)

1. cPanel → **Git™ Version Control**
2. Repo listesinde SSFF-02'nin yanındaki **Manage** tıkla
3. **Pull or Deploy** sekmesi
4. **Update from Remote** tıkla

Bu kadar! Dosyalar sunucuya çekildi.

> **Alternatif: SSH ile Terminal'den Çekme**
> cPanel → Terminal aç:
> ```bash
> cd ~/public_html/SSFF-02
> git pull origin main
> ```

---

## BÖLÜM 4: Veritabanını cPanel'e Aktarma

### 4.1 — cPanel'de Veritabanı Oluştur

1. cPanel → **MySQL® Databases**
2. **Create New Database** bölümünde:
   - Database name: `fitness101` yaz (hosting prefix eklenir, örn: `cpuser_fitness101`)
   - **Create Database** tıkla
3. **MySQL Users** bölümünde:
   - Username: `fitness101user` (veya istediğin)
   - Password: **güçlü bir şifre** gir ve not et
   - **Create User** tıkla
4. **Add User To Database** bölümünde:
   - User: az önce oluşturduğun kullanıcı
   - Database: az önce oluşturduğun veritabanı
   - **Add** tıkla
5. **ALL PRIVILEGES** işaretle → **Make Changes**

### 4.2 — database.sql'i İçe Aktar

1. cPanel → **phpMyAdmin**
2. Sol panelde oluşturduğun veritabanını tıkla (örn: `cpuser_fitness101`)
3. Üst menüde **İçe Aktar (Import)** sekmesine tıkla
4. **Dosya Seç** → bilgisayarından `database.sql` dosyasını seç
5. **Git (Go)** butonuna tıkla
6. "İçe aktarma başarıyla tamamlandı" mesajını gör

> **Önemli:** `database.sql` dosyasının en başındaki `CREATE DATABASE` ve `USE` satırlarını
> silmen gerekebilir çünkü cPanel'de veritabanını zaten elle oluşturdun.
> phpMyAdmin'de veritabanını seçtikten sonra sadece tablo ve veri komutları çalışır.

### 4.3 — config.php'yi Güncelle

cPanel'deki `config.php` dosyasını sunucu bilgileriyle güncelle.

cPanel → **File Manager** → projenin klasörüne git → `config.php` dosyasını düzenle:

```php
$conn = new mysqli('localhost', 'cpuser_fitness101user', 'SIFREN', 'cpuser_fitness101', 3306);
```

Değiştirmen gereken 3 değer:

| Parametre | Localhost (XAMPP) | cPanel (Sunucu) |
|-----------|------------------|-----------------|
| Host      | `localhost`      | `localhost`      |
| Kullanıcı | `root`           | `cpuser_fitness101user` |
| Şifre     | `''` (boş)       | `GucluSifre123!` |
| Veritabanı| `fitness101`     | `cpuser_fitness101` |
| Port      | `3306`           | `3306`           |

> **Dikkat:** Bu değişikliği sadece sunucuda yap. Lokaldeki config.php'yi değiştirme,
> yoksa kendi bilgisayarında çalışmaz.

### 4.4 — config.php'yi Git'ten Hariç Tutma (Önerilen)

Sunucu ve lokal farklı config kullandığı için config.php'yi git'ten çıkarmak mantıklıdır:

```powershell
# Proje klasöründe .gitignore dosyası oluştur
cd C:\xampp\htdocs\SSFF-02
```

`.gitignore` dosyası oluştur ve içine yaz:
```
config.php
```

Sonra config.php'yi git takibinden çıkar:
```powershell
git rm --cached config.php
git add .gitignore
git commit -m "config.php gitignore'a eklendi"
git push
```

Artık config.php hem lokalde hem sunucuda ayrı ayrı kalır ve git tarafından ezilmez.

---

## BÖLÜM 5: Tam Deploy Akışı (Özet Checklist)

Her güncelleme için bu adımları takip et:

### Lokalde (Bilgisayarında):
```
1. Kodu değiştir ve test et (localhost/SSFF-02)
2. git add .
3. git commit -m "Değişiklik açıklaması"
4. git push
```

### Sunucuda (cPanel):
```
5. cPanel → Git Version Control → Update from Remote
6. (Veritabanı değiştiyse) phpMyAdmin → SQL sekmesi → yeni sorguları çalıştır
7. Test et (seninsiten.com/SSFF-02)
```

---

## BÖLÜM 6: Veritabanı Değişikliklerini Yönetme

Projede yeni tablo veya kolon eklediğinde:

### Seçenek A: config.php Otomatik Migration (Mevcut Yöntem)

`config.php` dosyasında `CREATE TABLE IF NOT EXISTS` sorguları var. Bu sayede
sunucuya push yaptığında yeni tablolar otomatik oluşur. Mevcut veriler korunur.

Bu yöntem basit projeler için yeterli ve zaten kurulu.

### Seçenek B: Manuel SQL Çalıştırma

Büyük değişiklikler için (kolon silme, tablo yapısı değiştirme):

1. Değişiklik SQL'ini yaz, örneğin:
   ```sql
   ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL;
   ```
2. cPanel → phpMyAdmin → veritabanını seç → **SQL** sekmesi
3. SQL'i yapıştır ve çalıştır

### Seçenek C: Tam Sıfırlama (Dikkat!)

Veritabanını tamamen sıfırlamak istersen (TÜM VERİLER SİLİNİR):

1. phpMyAdmin → veritabanını seç → tüm tabloları seç → **Sil (Drop)**
2. **İçe Aktar** → `database.sql` dosyasını tekrar yükle

---

## BÖLÜM 7: Sık Karşılaşılan Sorunlar

### "Authentication failed" hatası
→ GitHub Personal Access Token süresi dolmuş olabilir.
→ Yeni token oluştur (Bölüm 1.4)

### cPanel'de "Repository already exists" hatası
→ Zaten oluşturulmuş. **Manage** → **Update from Remote** ile güncelle.

### Sunucuda sayfa boş veya hata veriyor
→ `config.php`'deki veritabanı bilgileri yanlış olabilir.
→ cPanel → Error Logs kontrol et.

### Sunucuda session çalışmıyor
→ cPanel → **Select PHP Version** → PHP 7.4+ olduğundan emin ol.
→ `session.save_path` ayarını kontrol et.

### Veritabanı tabloları eksik
→ Sayfayı bir kere aç (config.php otomatik oluşturur).
→ Veya phpMyAdmin'den `database.sql`'i tekrar içe aktar.

### Git pull sonrası config.php ezildi
→ Bölüm 4.4'teki `.gitignore` adımlarını uygula.
→ config.php'yi sunucuda tekrar düzenle.

---

## Hızlı Referans Komutları

```powershell
# ---- LOKAL (Bilgisayar) ----

# Durumu gör
git status

# Tüm değişiklikleri ekle + commit + push
git add . && git commit -m "güncelleme" && git push

# Son commit'leri gör
git log --oneline -5

# Değişiklikleri geri al (commit etmeden önce)
git checkout -- dosya_adi.php

# ---- SUNUCU (cPanel Terminal) ----

# Güncellemeleri çek
cd ~/public_html/SSFF-02 && git pull origin main

# Veritabanına SQL çalıştır
mysql -u KULLANICI -p VERITABANI < database.sql
```

---

## Giriş Bilgileri Hatırlatma

| Kullanıcı | Şifre     | Yetki |
|-----------|-----------|-------|
| admin     | admin123  | Admin |
| demo      | Demo1234  | Kullanıcı |

> **Sunucuya deploy ettikten sonra bu şifreleri mutlaka değiştir!**
