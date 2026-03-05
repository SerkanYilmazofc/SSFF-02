<?php
require_once 'config.php';
require_once 'session-check.php';

$programs = [];
$q = $conn->query("SELECT p.id, p.name, p.description, p.duration_type, p.custom_days,
                    (SELECT COUNT(*) FROM program_exercises e WHERE e.program_id=p.id) as exercise_count,
                    u.full_name as trainer_name, u.username as trainer_username
                    FROM program_templates p
                    LEFT JOIN users u ON u.id = p.created_by
                    ORDER BY p.id DESC");
if ($q) $programs = $q->fetch_all(MYSQLI_ASSOC);

$S = getAllSettings();
function ss($key, $def='') { global $S; return $S[$key] ?? $def; }

function durLabel($type, $custom) {
    if ($type === 'haftalik') return 'Haftalık';
    if ($type === 'aylik') return 'Aylık';
    if ($type === 'uc_aylik') return '3 Aylık';
    return 'Özel (' . ($custom ?: 0) . ' gün)';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FİTNESS101 - Fitness Merkezi</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">FİTNESS101</div>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Anasayfa</a></li>
                <li><a href="user-dashboard.php">Panelim</a></li>
                <li><a href="programs.html">Programlar</a></li>
                <li><a href="contact.php">İletişim</a></li>
                <?php if (!isLoggedIn()): ?>
                    <li><a href="login.html" class="admin-btn">Giriş Yap</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1><?= htmlspecialchars(ss('hero_title', 'FİTNESS101')) ?></h1>
            <p><?= htmlspecialchars(ss('hero_subtitle', 'Sağlıklı Yaşam Yolculuğunuza Başlayın')) ?></p>
            <a href="#programs" class="btn btn-primary">Programları Gör</a>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2>Neden FİTNESS101?</h2>
            <div class="features-grid">
                <?php for ($fi = 1; $fi <= 4; $fi++): ?>
                <div class="feature-card">
                    <div class="feature-icon"><?= htmlspecialchars(ss("feature_{$fi}_icon")) ?></div>
                    <h3><?= htmlspecialchars(ss("feature_{$fi}_title")) ?></h3>
                    <p><?= htmlspecialchars(ss("feature_{$fi}_desc")) ?></p>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <section id="programs" class="programs">
        <div class="container">
            <h2>Antrenman Programları</h2>
            <?php if (empty($programs)): ?>
                <p style="text-align:center;color:var(--text-light);">Henüz program eklenmemiş.</p>
            <?php else: ?>
                <div class="programs-grid">
                    <?php foreach ($programs as $p): ?>
                        <div class="program-card">
                            <div class="program-header">
                                <h3><?= htmlspecialchars($p['name']) ?></h3>
                                <span class="program-badge"><?= durLabel($p['duration_type'], $p['custom_days']) ?></span>
                            </div>
                            <div class="program-body">
                                <?php $tName = $p['trainer_name'] ?: $p['trainer_username']; ?>
                                <?php if ($tName): ?>
                                    <div style="color:var(--primary-color);font-size:.85rem;margin-bottom:6px;font-weight:600">Antrenör: <?= htmlspecialchars($tName) ?></div>
                                <?php endif; ?>
                                <p><?= htmlspecialchars($p['description'] ?: 'Detaylar için programa katılın.') ?></p>
                                <ul class="program-features">
                                    <li><?= (int)$p['exercise_count'] ?> hareket içerir</li>
                                    <li>Günlük seri takibi</li>
                                    <li>YouTube video destekli hareketler</li>
                                </ul>
                                <?php if (isLoggedIn()): ?>
                                    <a href="user-dashboard.php?program_id=<?= (int)$p['id'] ?>" class="btn btn-primary">Antrenman Başla</a>
                                <?php else: ?>
                                    <a href="login.html" class="btn btn-primary">Giriş Yap ve Başla</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="stats">
        <div class="container">
            <?php for ($si = 1; $si <= 3; $si++): ?>
            <div class="stat-item">
                <h3><?= htmlspecialchars(ss("stat_{$si}_value")) ?></h3>
                <p><?= htmlspecialchars(ss("stat_{$si}_label")) ?></p>
            </div>
            <?php endfor; ?>
            <div class="stat-item">
                <h3><?= count($programs) ?>+</h3>
                <p>Programlar</p>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container">
            <h2><?= htmlspecialchars(ss('cta_title', 'Bugün Başlayın!')) ?></h2>
            <p><?= htmlspecialchars(ss('cta_subtitle', 'Sağlıklı yaşama ilk adımı atın')) ?></p>
            <?php if (isLoggedIn()): ?>
                <a href="user-dashboard.php" class="btn btn-primary">Panelime Git</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary">Ücretsiz Kayıt Ol</a>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 FİTNESS101. Tüm hakları saklıdır.</p>
            <div class="footer-links">
                <a href="#">Gizlilik Politikası</a>
                <a href="#">Kullanım Koşulları</a>
                <a href="contact.php">İletişim</a>
            </div>
        </div>
    </footer>
</body>
</html>
