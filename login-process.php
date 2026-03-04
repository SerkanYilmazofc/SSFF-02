<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: login.html?error=' . urlencode('Lutfen tum alanlari doldurun.'));
    exit;
}

$stmt = $conn->prepare("SELECT id, username, email, full_name, password, is_admin, is_active FROM users WHERE (username = ? OR email = ?) LIMIT 1");
$stmt->bind_param('ss', $username, $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$user || (int)$user['is_active'] !== 1) {
    header('Location: login.html?error=' . urlencode('Kullanici adi veya sifre yanlis.'));
    exit;
}

if (hash('sha256', $password) !== $user['password']) {
    header('Location: login.html?error=' . urlencode('Kullanici adi veya sifre yanlis.'));
    exit;
}

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['is_admin'] = ((int)$user['is_admin'] === 1);
$_SESSION['logged_in'] = true;
$_SESSION['login_time'] = time();

$up = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$up->bind_param('i', $user['id']);
$up->execute();
$up->close();

if ($_SESSION['is_admin']) {
    header('Location: admin.php');
} else {
    header('Location: user-dashboard.php');
}
exit;
?>
