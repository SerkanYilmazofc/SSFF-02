<?php
require_once 'config.php';
require_once 'session-check.php';
$S = getAllSettings();
function cs($key, $def='') { global $S; return $S[$key] ?? $def; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İletişim | FİTNESS101</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .contact-msg{display:none;padding:12px;border-radius:8px;margin-bottom:14px;font-size:.92rem;text-align:center}
        .contact-msg.ok{display:block;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:#86efac}
        .contact-msg.err{display:block;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5}
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">FİTNESS101</div>
            <ul class="nav-links">
                <li><a href="index.php">Anasayfa</a></li>
                <li><a href="user-dashboard.php">Panelim</a></li>
                <li><a href="programs.html">Programlar</a></li>
                <li><a href="contact.php" class="active">İletişim</a></li>
            </ul>
        </div>
    </nav>

    <section class="hero hero-contact">
        <div class="hero-content">
            <h1>İletişim</h1>
            <p>Bizimle bağlantı kurun</p>
        </div>
    </section>

    <section class="contact-section">
        <div class="container">
            <div class="contact-wrapper">
                <div class="contact-info">
                    <h2>İletişim Bilgileri</h2>
                    <div class="info-item">
                        <h3>📍 Adres</h3>
                        <p><?= nl2br(htmlspecialchars(cs('contact_address', "Merkez Mah. Fitness Cad. No: 123\nİstanbul, Türkiye"))) ?></p>
                    </div>
                    <div class="info-item">
                        <h3>📞 Telefon</h3>
                        <p><?= nl2br(htmlspecialchars(cs('contact_phone', "(0212) 555-0123\n(0216) 666-0456"))) ?></p>
                    </div>
                    <div class="info-item">
                        <h3>📧 Email</h3>
                        <p><?= nl2br(htmlspecialchars(cs('contact_email', "info@fitness101.com\ndestek@fitness101.com"))) ?></p>
                    </div>
                    <div class="info-item">
                        <h3>🕐 Çalışma Saatleri</h3>
                        <p><?= nl2br(htmlspecialchars(cs('contact_hours', "Pazartesi-Cuma: 06:00 - 23:00\nCumartesi-Pazar: 08:00 - 22:00"))) ?></p>
                    </div>
                </div>

                <div class="contact-form">
                    <h2>Bize Yazın</h2>
                    <div id="contactMsg" class="contact-msg"></div>
                    <form id="contactForm">
                        <input type="text" name="name" placeholder="Adınız" required>
                        <input type="email" name="email" placeholder="Email Adresiniz" required>
                        <input type="tel" name="phone" placeholder="Telefon Numaranız">
                        <textarea name="message" placeholder="Mesajınız" rows="6" required></textarea>
                        <button type="submit" class="btn btn-primary" id="contactBtn">Gönder</button>
                    </form>
                </div>
            </div>
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

    <script>
    var form = document.getElementById('contactForm');
    var msgBox = document.getElementById('contactMsg');
    var btn = document.getElementById('contactBtn');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        btn.disabled = true;
        btn.textContent = 'Gönderiliyor...';
        msgBox.className = 'contact-msg';

        var fd = new FormData(form);
        fd.append('action', 'send_contact');

        fetch('api.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    msgBox.className = 'contact-msg ok';
                    msgBox.textContent = res.message || 'Mesajınız gönderildi!';
                    form.reset();
                } else {
                    msgBox.className = 'contact-msg err';
                    msgBox.textContent = res.error || 'Bir hata oluştu.';
                }
            })
            .catch(function() {
                msgBox.className = 'contact-msg err';
                msgBox.textContent = 'Sunucu hatası. Lütfen daha sonra deneyin.';
            })
            .finally(function() {
                btn.disabled = false;
                btn.textContent = 'Gönder';
            });
    });
    </script>
</body>
</html>
