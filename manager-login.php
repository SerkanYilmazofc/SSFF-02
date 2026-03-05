<?php
require_once 'session-check.php';
requireAdmin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['manager_pass'] ?? '';
    $correctPass = getSetting('manager_password', '123');
    if ($pass === $correctPass) {
        $_SESSION['manager_auth'] = true;
        header('Location: super-admin.php');
        exit;
    } else {
        $error = 'Yönetici şifresi yanlış!';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Girişi | FİTNESS101</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body{background:#050505;color:#f0f0f0;display:flex;align-items:center;justify-content:center;min-height:100vh}
        .login-box{background:#0d0d0d;border:1px solid #1a1a1a;border-radius:14px;padding:2.5rem;width:100%;max-width:400px;text-align:center}
        .login-box h1{font-size:1.5rem;color:#a78bfa;margin-bottom:.5rem}
        .login-box p{color:#9ca3af;font-size:.9rem;margin-bottom:1.5rem}
        .login-box input{width:100%;padding:12px;border:1px solid #1f1f1f;border-radius:8px;background:#050505;color:#f0f0f0;font-size:1rem;text-align:center;letter-spacing:4px;margin-bottom:12px}
        .login-box input:focus{outline:none;border-color:#a78bfa}
        .login-box button{width:100%;padding:12px;background:#a78bfa;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:1rem;cursor:pointer}
        .login-box button:hover{background:#8b5cf6}
        .err{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5;padding:10px;border-radius:8px;margin-bottom:12px;font-size:.88rem}
        .back{display:inline-block;margin-top:14px;color:#9ca3af;text-decoration:none;font-size:.85rem}
        .back:hover{color:#a78bfa}
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Yönetici Girişi</h1>
        <p>Yönetici paneline erişmek için şifreyi girin</p>
        <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <input type="password" name="manager_pass" placeholder="Yönetici Şifresi" autofocus required>
            <button type="submit">Giriş Yap</button>
        </form>
        <a class="back" href="admin.php">← Admin Panele Dön</a>
    </div>
</body>
</html>
