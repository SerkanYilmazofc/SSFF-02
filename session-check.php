<?php
require_once 'config.php';

function isLoggedIn() {
    return !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function isAdmin() {
    return isLoggedIn() && !empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.html?error=' . urlencode('Lutfen giris yapin.'));
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php?error=' . urlencode('Bu sayfaya erisim yetkiniz yok.'));
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => (int)($_SESSION['user_id'] ?? 0),
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? '',
        'is_admin' => (bool)($_SESSION['is_admin'] ?? false),
        'gender' => $_SESSION['gender'] ?? 'Erkek'
    ];
}
?>
