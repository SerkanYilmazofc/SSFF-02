<?php
// FİTNESS101 - Çıkış İşlemi

require_once 'config.php';

// Tüm session verilerini sil
$_SESSION = array();

// Session cookie'sini sil
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Remember me cookie'lerini sil
setcookie('remember_token', '', time() - 3600, '/');
setcookie('user_id', '', time() - 3600, '/');

// Session'ı yok et
session_destroy();

// Anasayfaya yönlendir
header('Location: index.php?logout=success');
exit();
?>
