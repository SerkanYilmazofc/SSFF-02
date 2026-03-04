<?php
// FİTNESS101 - Kaydolma İşlemi

require_once 'config.php';

// POST kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit();
}

// Form verilerini al ve temizle
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$age = isset($_POST['age']) ? (int)$_POST['age'] : null;
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : null;
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

// Validasyon
$errors = array();

if (empty($username)) {
    $errors[] = 'Kullanıcı adı boş olamaz!';
} elseif (strlen($username) < 3) {
    $errors[] = 'Kullanıcı adı en az 3 karakter olmalıdır!';
} elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
    $errors[] = 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir!';
}

if (empty($email)) {
    $errors[] = 'Email boş olamaz!';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Geçerli bir email adresi giriniz!';
}

if (empty($password)) {
    $errors[] = 'Şifre boş olamaz!';
} elseif (strlen($password) < 8) {
    $errors[] = 'Şifre en az 8 karakter olmalıdır!';
} elseif (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password)) {
    $errors[] = 'Şifre büyük ve küçük harf içermelidir!';
} elseif (!preg_match('/\d/', $password)) {
    $errors[] = 'Şifre en az bir rakam içermelidir!';
}

if ($password !== $password_confirm) {
    $errors[] = 'Şifreler eşleşmiyor!';
}

// Hata varsa önceki sayfaya yönlendir
if (!empty($errors)) {
    $error_msg = urlencode($errors[0]);
    header('Location: register.html?error=' . $error_msg);
    exit();
}

// SQL injection koruması
$username = $conn->real_escape_string($username);
$email = $conn->real_escape_string($email);
$full_name = $conn->real_escape_string($full_name);
$phone = $conn->real_escape_string($phone);
$address = $conn->real_escape_string($address);

// Kullanıcı adı veya email zaten var mı kontrol et
$check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email' LIMIT 1";
$check_result = $conn->query($check_query);

if ($check_result && $check_result->num_rows > 0) {
    header('Location: register.html?error=' . urlencode('Bu kullanıcı adı veya email zaten kullanılıyor!'));
    exit();
}

// Şifreyi hash'le (SHA2-256)
$hashed_password = hash('sha256', $password);

// Veritabanına kaydet
$insert_query = "INSERT INTO users (username, email, password, full_name, phone, age, gender, address, is_active) 
                 VALUES ('$username', '$email', '$hashed_password', '$full_name', '$phone', " . ($age ?: 'NULL') . ", " . ($gender ? "'$gender'" : 'NULL') . ", '$address', TRUE)";

if ($conn->query($insert_query)) {
    // Başarılı kayıt
    $user_id = $conn->insert_id;
    
    // Otomatik session oluştur (opsiyonel - kullanıcı email doğrulaması yapacaksa bu satırları yorum yap)
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['logged_in'] = true;
    
    header('Location: login.html?success=' . urlencode('Hesapınız başarıyla oluşturuldu! Hoş geldiniz, ' . $full_name));
    exit();
    
} else {
    // Veritabanı hatası
    error_log('Register Error: ' . $conn->error);
    header('Location: register.html?error=' . urlencode('Bir hata oluştu. Lütfen daha sonra tekrar deneyiniz!'));
    exit();
}

$conn->close();
?>
