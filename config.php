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
    $conn->query("INSERT INTO users (username,email,password,full_name,phone,age,gender,is_admin,is_active) VALUES ('demo','demo@fitness101.com',SHA2('Demo1234',256),'Demo Kullanıcı','555-0000',25,'Erkek',0,1)");
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

// Eski yapida farkli kolon adlari varsa yeni panel icin ekleyelim.
$conn->query("ALTER TABLE workout_history ADD COLUMN IF NOT EXISTS duration_min INT DEFAULT 0");
$conn->query("ALTER TABLE workout_history ADD COLUMN IF NOT EXISTS calories INT DEFAULT 0");
$conn->query("ALTER TABLE workout_history ADD COLUMN IF NOT EXISTS workout_date DATETIME DEFAULT CURRENT_TIMESTAMP");
?>
