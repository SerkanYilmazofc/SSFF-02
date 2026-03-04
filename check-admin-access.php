<?php
require_once 'session-check.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['authorized' => false, 'message' => 'Lutfen giris yapiniz!']);
    exit;
}

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['authorized' => false, 'message' => 'Bu sayfaya erisim yetkiniz yok.']);
    exit;
}

echo json_encode(['authorized' => true, 'user' => getCurrentUser()]);
?>
