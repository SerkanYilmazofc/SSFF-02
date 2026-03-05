<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Europe/Istanbul');

$conn = new mysqli('localhost', 'root', '', 'fitness101', 3306);
if ($conn->connect_error) {
    die('Veritabani baglanti hatasi: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

$conn->query("CREATE TABLE IF NOT EXISTS users (
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
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$chk = $conn->query("SELECT COUNT(*) c FROM users WHERE username='admin'");
if ($chk && (int)$chk->fetch_assoc()['c'] === 0) {
    $conn->query("INSERT INTO users (username,email,password,full_name,is_admin,is_active) VALUES ('admin','admin@fitness101.com',SHA2('admin123',256),'Admin Kullanıcı',1,1)");
}
$chk2 = $conn->query("SELECT COUNT(*) c FROM users WHERE username='demo'");
if ($chk2 && (int)$chk2->fetch_assoc()['c'] === 0) {
    $conn->query("INSERT INTO users (username,email,password,full_name,phone,age,gender,is_admin,is_active,trainer_id) VALUES ('demo','demo@fitness101.com',SHA2('Demo1234',256),'Demo Kullanıcı','555-0000',25,'Erkek',0,1,1)");
}

$conn->query("CREATE TABLE IF NOT EXISTS program_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    description TEXT,
    duration_type ENUM('haftalik','aylik','uc_aylik','ozel') NOT NULL,
    custom_days INT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS program_exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT NOT NULL,
    title VARCHAR(120) NOT NULL,
    description TEXT,
    youtube_url VARCHAR(255),
    sort_order INT DEFAULT 1
)");

$conn->query("CREATE TABLE IF NOT EXISTS user_program_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    program_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    selected_days VARCHAR(50) NOT NULL,
    status ENUM('Aktif','Tamamlandı','İptal') DEFAULT 'Aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS assignment_schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    assignment_id INT NOT NULL,
    scheduled_date DATE NOT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    completed_at DATETIME DEFAULT NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS workout_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    program_name VARCHAR(120) NOT NULL,
    duration_min INT DEFAULT 0,
    calories INT DEFAULT 0,
    notes TEXT,
    workout_date DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS trainer_id INT DEFAULT NULL");

$conn->query("CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$defCheck = $conn->query("SELECT COUNT(*) c FROM site_settings");
if ($defCheck && (int)$defCheck->fetch_assoc()['c'] === 0) {
    $defaults = [
        ['hero_title', 'FİTNESS101'],
        ['hero_subtitle', 'Sağlıklı Yaşam Yolculuğunuza Başlayın'],
        ['feature_1_icon', '💪'], ['feature_1_title', 'Profesyonel Antrenörlük'], ['feature_1_desc', 'Sertifikalı antrenörlerle kişiye özel antrenman programları'],
        ['feature_2_icon', '🏥'], ['feature_2_title', 'Beslenme Danışmanlığı'], ['feature_2_desc', 'Uzman diyetisyenlerle beslenme planlaması'],
        ['feature_3_icon', '🎯'], ['feature_3_title', 'Hedef Yönetimi'], ['feature_3_desc', 'Kişisel hedeflerinize ulaşmak için özelleştirilmiş planlar'],
        ['feature_4_icon', '📱'], ['feature_4_title', 'Program Takibi'], ['feature_4_desc', 'Günlük seri takibi ve ilerleme görüntüleme'],
        ['stat_1_value', '5000+'], ['stat_1_label', 'Mutlu Üye'],
        ['stat_2_value', '98%'], ['stat_2_label', 'Başarı Oranı'],
        ['stat_3_value', '24/7'], ['stat_3_label', 'Destek'],
        ['cta_title', 'Bugün Başlayın!'], ['cta_subtitle', 'Sağlıklı yaşama ilk adımı atın'],
        ['contact_address', "Merkez Mah. Fitness Cad. No: 123\nİstanbul, Türkiye"],
        ['contact_phone', "(0212) 555-0123\n(0216) 666-0456"],
        ['contact_email', "info@fitness101.com\ndestek@fitness101.com"],
        ['contact_hours', "Pazartesi-Cuma: 06:00 - 23:00\nCumartesi-Pazar: 08:00 - 22:00"],
        ['smtp_host', ''], ['smtp_port', '587'], ['smtp_user', ''], ['smtp_pass', ''], ['smtp_from_email', ''], ['smtp_from_name', 'FİTNESS101'],
        ['manager_password', '123'],
    ];
    $ins = $conn->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($defaults as $d) {
        $ins->bind_param('ss', $d[0], $d[1]);
        $ins->execute();
    }
    $ins->close();
}

$conn->query("CREATE TABLE IF NOT EXISTS daily_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    log_date DATE NOT NULL,
    weight DECIMAL(5,2) DEFAULT NULL,
    height DECIMAL(5,1) DEFAULT NULL,
    calories_in INT DEFAULT 0,
    waist DECIMAL(5,1) DEFAULT NULL,
    neck DECIMAL(5,1) DEFAULT NULL,
    hip DECIMAL(5,1) DEFAULT NULL,
    chest DECIMAL(5,1) DEFAULT NULL,
    arm DECIMAL(5,1) DEFAULT NULL,
    shoulder DECIMAL(5,1) DEFAULT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_date (user_id, log_date)
) ENGINE=InnoDB");

function getSetting($key, $default = '') {
    global $conn;
    $s = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key=? LIMIT 1");
    $s->bind_param('s', $key);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $s->close();
    return $r ? $r['setting_value'] : $default;
}

function getAllSettings() {
    global $conn;
    $q = $conn->query("SELECT setting_key, setting_value FROM site_settings");
    $out = [];
    if ($q) { while ($r = $q->fetch_assoc()) $out[$r['setting_key']] = $r['setting_value']; }
    return $out;
}
?>
