-- ================================================================
-- FİTNESS101 — Veritabanı Kurulum Scripti
-- Veritabanı adı: fitness101
-- ================================================================

-- cPanel'de bu iki satır gerekli değil (veritabanını zaten cPanel'den oluşturdun):
-- CREATE DATABASE IF NOT EXISTS fitness101 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE fitness101;

-- ================================================================
-- 1. Kullanıcılar Tablosu
-- ================================================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(64) NOT NULL,
    full_name VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    age INT DEFAULT NULL,
    gender ENUM('Erkek','Kadın','Diğer') DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    trainer_id INT DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================================================================
-- 2. Program Şablonları
-- ================================================================
CREATE TABLE IF NOT EXISTS program_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    description TEXT,
    duration_type ENUM('haftalik','aylik','uc_aylik','ozel') NOT NULL,
    custom_days INT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================================================================
-- 3. Program Hareketleri
-- ================================================================
CREATE TABLE IF NOT EXISTS program_exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT NOT NULL,
    title VARCHAR(120) NOT NULL,
    description TEXT,
    youtube_url VARCHAR(255),
    sort_order INT DEFAULT 1
) ENGINE=InnoDB;

-- ================================================================
-- 4. Kullanıcı Program Atamaları
-- ================================================================
CREATE TABLE IF NOT EXISTS user_program_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    program_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    selected_days VARCHAR(50) NOT NULL,
    status ENUM('Aktif','Tamamlandı','İptal') DEFAULT 'Aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================================================================
-- 5. Atama Takvimi (günlük seri takibi)
-- ================================================================
CREATE TABLE IF NOT EXISTS assignment_schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    assignment_id INT NOT NULL,
    scheduled_date DATE NOT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    completed_at DATETIME DEFAULT NULL
) ENGINE=InnoDB;

-- ================================================================
-- 6. Antrenman Geçmişi
-- ================================================================
CREATE TABLE IF NOT EXISTS workout_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    program_name VARCHAR(120) NOT NULL,
    duration_min INT DEFAULT 0,
    calories INT DEFAULT 0,
    notes TEXT,
    workout_date DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================================================================
-- 7. Demo Kullanıcılar
--    Şifreler SHA-256 hash ile saklanır.
--    admin   -> admin123    (SHA256: 240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9)
--    demo    -> Demo1234    (SHA256: a9e0b2f89ae83f1e38afa37cc5ceac4e27f82345f2dbb1b tried...)
-- ================================================================

INSERT INTO users (username, email, password, full_name, is_admin, is_active)
VALUES ('admin', 'admin@fitness101.com', SHA2('admin123', 256), 'Admin Kullanıcı', 1, 1)
ON DUPLICATE KEY UPDATE username=username;

INSERT INTO users (username, email, password, full_name, phone, age, gender, is_admin, is_active, trainer_id)
VALUES ('demo', 'demo@fitness101.com', SHA2('Demo1234', 256), 'Demo Kullanıcı', '555-0000', 25, 'Erkek', 0, 1, 1)
ON DUPLICATE KEY UPDATE username=username;

-- ================================================================
-- 8. Demo Program Şablonları
-- ================================================================
INSERT INTO program_templates (name, description, duration_type, custom_days, created_by) VALUES
('Haftalik Guc Programi', 'Kas inşası ve gücü artırmak için tasarlanmış haftalık program. Başlangıç ve orta seviye için uygundur.', 'haftalik', NULL, 1),
('Aylik Kardiyo Programi', 'Yüksek yoğunluklu interval antrenmanı ile yağ yakma ve dayanıklılık artırma odaklı 30 günlük program.', 'aylik', NULL, 1),
('3 Aylik Full Body', 'Tüm vücudu hedefleyen kapsamlı 3 aylık dönüşüm programı. İleri seviye için tasarlanmıştır.', 'uc_aylik', NULL, 1),
('Yoga ve Esneklik (45 Gun)', 'Esneklik, denge ve zihinsel rahatlama odaklı özel süreli program.', 'ozel', 45, 1);

-- ================================================================
-- 9. Demo Hareketler
-- ================================================================
INSERT INTO program_exercises (program_id, title, description, youtube_url, sort_order) VALUES
(1, 'Squat', 'Bacak ve kalça kaslarını hedefleyen temel bileşik hareket.', 'https://www.youtube.com/watch?v=aclHkVaku9U', 1),
(1, 'Bench Press', 'Göğüs, omuz ve triceps kaslarını çalıştıran temel üst vücut hareketi.', 'https://www.youtube.com/watch?v=rT7DgCr-3pg', 2),
(1, 'Deadlift', 'Sırt, bacak ve core kaslarını güçlendiren en etkili bileşik hareket.', 'https://www.youtube.com/watch?v=op9kVnSso6Q', 3),
(1, 'Shoulder Press', 'Omuz kaslarını hedefleyen temel pres hareketi.', 'https://www.youtube.com/watch?v=qEwKCR5JCog', 4),
(2, 'Burpee', 'Tüm vücudu çalıştıran yüksek yoğunluklu kardiyovasküler hareket.', 'https://www.youtube.com/watch?v=TU8QYVW0gDU', 1),
(2, 'Mountain Climber', 'Core ve kardiyovasküler dayanıklılık için etkili hareket.', 'https://www.youtube.com/watch?v=nmwgirgXLYM', 2),
(2, 'Jump Squat', 'Patlayıcı güç ve kardiyoyu birleştiren squat varyasyonu.', 'https://www.youtube.com/watch?v=CVaEhXotL7M', 3),
(3, 'Pull Up', 'Sırt ve biceps kaslarını güçlendiren üst vücut hareketi.', 'https://www.youtube.com/watch?v=eGo4IYlbE5g', 1),
(3, 'Lunge', 'Bacak kaslarını tek taraflı çalıştıran denge hareketi.', 'https://www.youtube.com/watch?v=QOVaHwm-Q6U', 2),
(3, 'Plank', 'Core stabilizasyonu için temel izometrik hareket.', 'https://www.youtube.com/watch?v=ASdvN_XEl_c', 3),
(3, 'Dips', 'Triceps ve göğüs alt kaslarını hedefleyen hareket.', 'https://www.youtube.com/watch?v=2z8JmcrW-As', 4),
(4, 'Cat-Cow Stretch', 'Omurga esnekliği ve rahatlama için temel yoga hareketi.', 'https://www.youtube.com/watch?v=kqnua4rHVVA', 1),
(4, 'Downward Dog', 'Tüm vücudu geren klasik yoga pozu.', 'https://www.youtube.com/watch?v=EC7RGJ975iM', 2),
(4, 'Warrior Pose', 'Bacak gücü ve denge için savaşçı pozu.', 'https://www.youtube.com/watch?v=k4qaVoAbeHM', 3);
