<?php
require_once 'session-check.php';
requireAdmin();

if (!isset($_SESSION['manager_auth']) || $_SESSION['manager_auth'] !== true) {
    header('Location: manager-login.php');
    exit;
}

$u = getCurrentUser();
$settings = getAllSettings();
function s($key) { global $settings; return $settings[$key] ?? ''; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli | FİTNESS101</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body{background:#050505;color:#f0f0f0}
        .wrap{max-width:1100px;margin:0 auto;padding:24px}
        .top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
        .top h1{margin:0;font-size:1.5rem;color:#a78bfa}
        .top p{margin:0;color:#9ca3af}
        .actions a{display:inline-block;padding:8px 12px;border-radius:8px;text-decoration:none;font-weight:600;font-size:.85rem;margin-left:6px}
        .actions .back{background:#111827;border:1px solid #1f2937;color:#cbd5e1}
        .actions .out{background:#7f1d1d;color:#fecaca}
        .tabs{display:flex;gap:6px;margin-bottom:16px;flex-wrap:wrap}
        .tabs button{padding:10px 16px;border:1px solid #1f1f1f;background:#0d0d0d;color:#9ca3af;border-radius:8px;cursor:pointer;font-weight:600;font-size:.85rem;transition:all .2s}
        .tabs button.active{background:#a78bfa;color:#fff;border-color:#a78bfa}
        .tabs button:hover{border-color:#a78bfa;color:#e0d4fc}
        .tab-content{display:none}
        .tab-content.active{display:block}
        .card{background:#0d0d0d;border:1px solid #1a1a1a;border-radius:14px;padding:16px;margin-bottom:16px}
        .card h2{margin:0 0 14px;font-size:1.05rem;color:#a78bfa}
        label{display:block;color:#9ca3af;font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;margin:10px 0 6px}
        input,select,textarea{width:100%;padding:10px;border:1px solid #1f1f1f;border-radius:8px;background:#050505;color:#f0f0f0;font-family:inherit;font-size:.92rem}
        textarea{min-height:60px;resize:vertical}
        .row2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
        .btn{margin-top:12px;padding:9px 14px;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:.88rem}
        .btn.primary{background:#a78bfa;color:#fff}
        .btn.primary:hover{background:#8b5cf6}
        .msg{display:none;padding:10px 12px;border-radius:8px;margin-bottom:10px;font-size:.84rem}
        .msg.ok{display:block;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:#86efac}
        .msg.err{display:block;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5}
        .feature-block{border:1px solid #1f1f1f;border-radius:10px;padding:12px;background:#080808;margin-bottom:10px}
        .feature-block h3{margin:0 0 8px;font-size:.9rem;color:#c4b5fd}
        .smtp-test{margin-top:8px;font-size:.84rem;color:#9ca3af}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px;border-bottom:1px solid #1a1a1a;text-align:left;font-size:.84rem}
        th{font-size:.72rem;text-transform:uppercase;color:#6b7280;letter-spacing:.04em}
        tr:hover{background:#0a0a0a}
        .badge{display:inline-block;padding:3px 8px;border-radius:14px;font-size:.72rem;font-weight:700}
        .badge.admin{background:rgba(167,139,250,.15);color:#c4b5fd}
        .badge.user{background:rgba(59,130,246,.12);color:#93c5fd}
        .badge.active{background:rgba(34,197,94,.12);color:#86efac}
        .badge.inactive{background:rgba(239,68,68,.12);color:#fca5a5}
        .modal{position:fixed;inset:0;background:rgba(0,0,0,.8);display:none;align-items:center;justify-content:center;z-index:9999}
        .modal.show{display:flex}
        .modal-body{background:#0d0d0d;border:1px solid #1f1f1f;border-radius:14px;width:min(640px,94vw);max-height:90vh;overflow:auto;padding:20px}
        .modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
        .modal-head h3{margin:0;color:#a78bfa;font-size:1.1rem}
        .close-btn{border:none;background:#1f2937;color:#cbd5e1;padding:6px 12px;border-radius:6px;cursor:pointer;font-weight:600;font-size:.82rem}
        .close-btn:hover{background:#374151}
        .btn.danger{background:#7f1d1d;color:#fecaca}
        .btn.danger:hover{background:#991b1b}
        .search-box{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;align-items:center}
        .search-box input{flex:1;min-width:200px}
        .search-box select{width:auto;min-width:120px}
        @media(max-width:700px){.row2,.row3{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <div>
            <h1>Yönetici Paneli</h1>
            <p>Site içeriklerini, iletişim bilgilerini ve email ayarlarını yönetin.</p>
        </div>
        <div class="actions">
            <a class="back" href="admin.php">Admin Panel</a>
            <a class="back" href="user-dashboard.php">Kullanıcı Paneli</a>
            <a class="out" href="logout.php">Çıkış</a>
        </div>
    </div>

    <div id="msg" class="msg"></div>

    <div class="tabs">
        <button class="active" data-tab="users">Kullanıcılar</button>
        <button data-tab="homepage">Anasayfa İçerikleri</button>
        <button data-tab="stats">İstatistikler & CTA</button>
        <button data-tab="contact">İletişim Bilgileri</button>
        <button data-tab="email">Email Ayarları</button>
        <button data-tab="security">Güvenlik</button>
    </div>

    <!-- Tab: Users -->
    <div id="tab-users" class="tab-content active">
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:14px">
                <h2 style="margin:0">Tüm Kullanıcılar</h2>
                <button class="btn primary" onclick="openCreateUser()">+ Yeni Kullanıcı Ekle</button>
            </div>
            <div class="search-box">
                <input type="text" id="userSearch" placeholder="İsim, email veya kullanıcı adı ara..." oninput="filterUsers()">
                <select id="userRoleFilter" onchange="filterUsers()">
                    <option value="">Tüm Roller</option>
                    <option value="admin">Antrenör / Admin</option>
                    <option value="user">Kullanıcı</option>
                </select>
                <select id="userStatusFilter" onchange="filterUsers()">
                    <option value="">Tüm Durum</option>
                    <option value="1">Aktif</option>
                    <option value="0">Pasif</option>
                </select>
            </div>
            <div style="overflow-x:auto">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ad Soyad</th>
                            <th>Kullanıcı Adı</th>
                            <th>Email</th>
                            <th>Yetki</th>
                            <th>Antrenör</th>
                            <th>Durum</th>
                            <th>Son Giriş</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="userRows"><tr><td colspan="9">Yükleniyor...</td></tr></tbody>
                </table>
            </div>
            <div id="userCount" style="margin-top:10px;font-size:.78rem;color:#6b7280"></div>
        </div>
    </div>

    <!-- Tab: Homepage -->
    <div id="tab-homepage" class="tab-content">
        <div class="card">
            <h2>Hero Bölümü</h2>
            <div class="row2">
                <div><label>Başlık</label><input id="hero_title" value="<?= htmlspecialchars(s('hero_title')) ?>"></div>
                <div><label>Alt Başlık</label><input id="hero_subtitle" value="<?= htmlspecialchars(s('hero_subtitle')) ?>"></div>
            </div>
        </div>

        <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="card feature-block">
            <h3>Özellik #<?= $i ?></h3>
            <div class="row3">
                <div><label>İkon (Emoji)</label><input id="feature_<?= $i ?>_icon" value="<?= htmlspecialchars(s("feature_{$i}_icon")) ?>"></div>
                <div><label>Başlık</label><input id="feature_<?= $i ?>_title" value="<?= htmlspecialchars(s("feature_{$i}_title")) ?>"></div>
                <div><label>Açıklama</label><input id="feature_<?= $i ?>_desc" value="<?= htmlspecialchars(s("feature_{$i}_desc")) ?>"></div>
            </div>
        </div>
        <?php endfor; ?>

        <button class="btn primary" onclick="saveGroup('homepage')">Anasayfa İçeriklerini Kaydet</button>
    </div>

    <!-- Tab: Stats & CTA -->
    <div id="tab-stats" class="tab-content">
        <div class="card">
            <h2>İstatistikler</h2>
            <p style="color:#6b7280;font-size:.82rem;margin-bottom:10px">Not: Program sayısı otomatik olarak veritabanından çekilir.</p>
            <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="row2" style="margin-bottom:8px">
                <div><label>Değer #<?= $i ?></label><input id="stat_<?= $i ?>_value" value="<?= htmlspecialchars(s("stat_{$i}_value")) ?>"></div>
                <div><label>Etiket #<?= $i ?></label><input id="stat_<?= $i ?>_label" value="<?= htmlspecialchars(s("stat_{$i}_label")) ?>"></div>
            </div>
            <?php endfor; ?>
        </div>

        <div class="card">
            <h2>CTA (Çağrı) Bölümü</h2>
            <div class="row2">
                <div><label>Başlık</label><input id="cta_title" value="<?= htmlspecialchars(s('cta_title')) ?>"></div>
                <div><label>Alt Başlık</label><input id="cta_subtitle" value="<?= htmlspecialchars(s('cta_subtitle')) ?>"></div>
            </div>
        </div>

        <button class="btn primary" onclick="saveGroup('stats')">İstatistik ve CTA Kaydet</button>
    </div>

    <!-- Tab: Contact -->
    <div id="tab-contact" class="tab-content">
        <div class="card">
            <h2>İletişim Bilgileri</h2>
            <label>Adres</label>
            <textarea id="contact_address" rows="3"><?= htmlspecialchars(s('contact_address')) ?></textarea>
            <label>Telefon</label>
            <textarea id="contact_phone" rows="2"><?= htmlspecialchars(s('contact_phone')) ?></textarea>
            <label>Email</label>
            <textarea id="contact_email" rows="2"><?= htmlspecialchars(s('contact_email')) ?></textarea>
            <label>Çalışma Saatleri</label>
            <textarea id="contact_hours" rows="2"><?= htmlspecialchars(s('contact_hours')) ?></textarea>
        </div>

        <button class="btn primary" onclick="saveGroup('contact')">İletişim Bilgilerini Kaydet</button>
    </div>

    <!-- Tab: Email -->
    <div id="tab-email" class="tab-content">
        <div class="card">
            <h2>SMTP / Email Ayarları</h2>
            <p style="color:#6b7280;font-size:.82rem;margin-bottom:10px">İletişim formundan gelen mesajlar bu ayarlarla gönderilecek. Boş bırakılırsa PHP mail() kullanılır.</p>
            <div class="row2">
                <div><label>SMTP Sunucu</label><input id="smtp_host" value="<?= htmlspecialchars(s('smtp_host')) ?>" placeholder="smtp.gmail.com"></div>
                <div><label>Port</label><input id="smtp_port" value="<?= htmlspecialchars(s('smtp_port')) ?>" placeholder="587"></div>
            </div>
            <div class="row2">
                <div><label>Kullanıcı Adı</label><input id="smtp_user" value="<?= htmlspecialchars(s('smtp_user')) ?>" placeholder="email@gmail.com"></div>
                <div><label>Şifre</label><input id="smtp_pass" type="password" value="<?= htmlspecialchars(s('smtp_pass')) ?>"></div>
            </div>
            <div class="row2">
                <div><label>Gönderici Email</label><input id="smtp_from_email" value="<?= htmlspecialchars(s('smtp_from_email')) ?>" placeholder="noreply@fitness101.com"></div>
                <div><label>Gönderici Adı</label><input id="smtp_from_name" value="<?= htmlspecialchars(s('smtp_from_name')) ?>" placeholder="FİTNESS101"></div>
            </div>
        </div>

        <button class="btn primary" onclick="saveGroup('email')">Email Ayarlarını Kaydet</button>
        <button class="btn primary" style="background:#059669;margin-left:8px" onclick="testEmail()">Test Email Gönder</button>
        <div id="emailTestResult" class="smtp-test"></div>
    </div>

    <!-- Tab: Security -->
    <div id="tab-security" class="tab-content">
        <div class="card">
            <h2>Yönetici Şifresi</h2>
            <p style="color:#6b7280;font-size:.82rem;margin-bottom:10px">Yönetici paneline giriş için kullanılan şifreyi değiştirin.</p>
            <label>Yeni Yönetici Şifresi</label>
            <input id="manager_password" type="password" value="<?= htmlspecialchars(s('manager_password')) ?>">
        </div>
        <button class="btn primary" onclick="saveGroup('security')">Şifreyi Güncelle</button>
    </div>
</div>

<!-- User Detail Modal -->
<div id="userModal" class="modal">
    <div class="modal-body">
        <div class="modal-head">
            <h3 id="userModalTitle">Kullanıcı Detay</h3>
            <button class="close-btn" onclick="closeUserModal()">Kapat</button>
        </div>
        <form id="userEditForm">
            <input type="hidden" id="ueId">
            <div class="row2">
                <div><label>Kullanıcı Adı *</label><input id="ueUsername" required></div>
                <div><label>Email *</label><input id="ueEmail" type="email" required></div>
            </div>
            <div class="row2">
                <div><label>Ad Soyad</label><input id="ueFullName"></div>
                <div><label>Telefon</label><input id="uePhone"></div>
            </div>
            <div class="row2">
                <div><label>Yaş</label><input id="ueAge" type="number" min="1" max="120"></div>
                <div><label>Cinsiyet</label>
                    <select id="ueGender">
                        <option value="">Seçilmedi</option>
                        <option value="Erkek">Erkek</option>
                        <option value="Kadın">Kadın</option>
                        <option value="Diğer">Diğer</option>
                    </select>
                </div>
            </div>
            <label>Adres</label>
            <input id="ueAddress">
            <div class="row2">
                <div><label>Yetki</label>
                    <select id="ueIsAdmin">
                        <option value="0">Kullanıcı</option>
                        <option value="1">Antrenör / Admin</option>
                    </select>
                </div>
                <div><label>Durum</label>
                    <select id="ueIsActive">
                        <option value="1">Aktif</option>
                        <option value="0">Pasif</option>
                    </select>
                </div>
            </div>
            <div class="row2">
                <div><label>Antrenör</label>
                    <select id="ueTrainerId"><option value="">Antrenör yok</option></select>
                </div>
                <div><label>Yeni Şifre</label><input id="ueNewPassword" type="password" placeholder="Değiştirmek için yazın..."></div>
            </div>
            <div id="ueExtraInfo" style="margin-top:12px;padding:10px;background:#080808;border:1px solid #1a1a1a;border-radius:8px;font-size:.82rem;color:#6b7280"></div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;flex-wrap:wrap;gap:8px">
                <button type="button" class="btn danger" id="ueDeleteBtn" onclick="deleteUser()">Kullanıcıyı Sil</button>
                <button type="submit" class="btn primary">Değişiklikleri Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="modal">
    <div class="modal-body" style="max-width:520px">
        <div class="modal-head">
            <h3>Yeni Kullanıcı Oluştur</h3>
            <button class="close-btn" onclick="closeCreateModal()">Kapat</button>
        </div>
        <form id="createUserForm">
            <div class="row2">
                <div><label>Kullanıcı Adı *</label><input id="cuUsername" required></div>
                <div><label>Email *</label><input id="cuEmail" type="email" required></div>
            </div>
            <div class="row2">
                <div><label>Ad Soyad</label><input id="cuFullName"></div>
                <div><label>Şifre *</label><input id="cuPassword" type="password" required></div>
            </div>
            <div class="row2">
                <div><label>Yetki</label>
                    <select id="cuIsAdmin">
                        <option value="0">Kullanıcı</option>
                        <option value="1">Antrenör / Admin</option>
                    </select>
                </div>
                <div></div>
            </div>
            <div style="text-align:right;margin-top:14px">
                <button type="submit" class="btn primary">Kullanıcı Oluştur</button>
            </div>
        </form>
    </div>
</div>

<script>
var tabBtns = document.querySelectorAll('.tabs button');
var tabPanes = document.querySelectorAll('.tab-content');
for (var i = 0; i < tabBtns.length; i++) {
    tabBtns[i].addEventListener('click', function() {
        for (var j = 0; j < tabBtns.length; j++) tabBtns[j].classList.remove('active');
        for (var k = 0; k < tabPanes.length; k++) tabPanes[k].classList.remove('active');
        this.classList.add('active');
        var t = document.getElementById('tab-' + this.getAttribute('data-tab'));
        if (t) t.classList.add('active');
    });
}

/* ---- User Management ---- */
var allUsers = [];
var trainerList = [];

function apiPost(action, payload) {
    var fd = new FormData();
    fd.append('action', action);
    if (payload) {
        var keys = Object.keys(payload);
        for (var i = 0; i < keys.length; i++) fd.append(keys[i], payload[keys[i]]);
    }
    return fetch('api.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .catch(function(err) { console.error(action, err); return { success: false }; });
}

function apiGet(action, extra) {
    var q = new URLSearchParams({ action: action });
    if (extra) { var keys = Object.keys(extra); for (var i = 0; i < keys.length; i++) q.append(keys[i], extra[keys[i]]); }
    return fetch('api.php?' + q.toString(), { credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .catch(function(err) { console.error(action, err); return { success: false }; });
}

function esc(str) { var d = document.createElement('div'); d.textContent = str || ''; return d.innerHTML; }

function loadAllUsers() {
    apiGet('manager_list_all_users').then(function(r) {
        allUsers = r.users || [];
        renderUsers();
    });
}

function loadTrainerList() {
    apiGet('list_trainers').then(function(r) {
        trainerList = r.trainers || [];
    });
}

function renderUsers() {
    var search = (document.getElementById('userSearch').value || '').toLowerCase();
    var roleFilter = document.getElementById('userRoleFilter').value;
    var statusFilter = document.getElementById('userStatusFilter').value;

    var filtered = allUsers.filter(function(u) {
        if (search) {
            var hay = ((u.full_name || '') + ' ' + u.username + ' ' + u.email).toLowerCase();
            if (hay.indexOf(search) === -1) return false;
        }
        if (roleFilter === 'admin' && parseInt(u.is_admin) !== 1) return false;
        if (roleFilter === 'user' && parseInt(u.is_admin) !== 0) return false;
        if (statusFilter === '1' && parseInt(u.is_active) !== 1) return false;
        if (statusFilter === '0' && parseInt(u.is_active) !== 0) return false;
        return true;
    });

    var tb = document.getElementById('userRows');
    if (filtered.length === 0) {
        tb.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#6b7280;padding:20px">Kullanıcı bulunamadı.</td></tr>';
        document.getElementById('userCount').textContent = '';
        return;
    }

    tb.innerHTML = filtered.map(function(u) {
        var isAdmin = parseInt(u.is_admin) === 1;
        var isActive = parseInt(u.is_active) === 1;
        var roleBadge = isAdmin ? '<span class="badge admin">Antrenör</span>' : '<span class="badge user">Kullanıcı</span>';
        var statusBadge = isActive ? '<span class="badge active">Aktif</span>' : '<span class="badge inactive">Pasif</span>';
        var trainer = u.trainer_name || u.trainer_username || '-';
        var lastLogin = u.last_login ? u.last_login.substring(0, 16).replace('T', ' ') : 'Hiç';
        return '<tr style="cursor:pointer" onclick="openUserDetail(' + u.id + ')">' +
            '<td style="color:#6b7280;font-size:.78rem">#' + u.id + '</td>' +
            '<td><strong style="color:#e2e8f0">' + esc(u.full_name || '-') + '</strong></td>' +
            '<td style="color:#93c5fd">' + esc(u.username) + '</td>' +
            '<td>' + esc(u.email) + '</td>' +
            '<td>' + roleBadge + '</td>' +
            '<td style="font-size:.82rem;color:#9ca3af">' + esc(trainer) + '</td>' +
            '<td>' + statusBadge + '</td>' +
            '<td style="font-size:.78rem;color:#6b7280">' + lastLogin + '</td>' +
            '<td><button class="btn primary" style="padding:4px 10px;font-size:.76rem;margin:0" onclick="event.stopPropagation();openUserDetail(' + u.id + ')">Detay</button></td>' +
            '</tr>';
    }).join('');

    var adminCount = filtered.filter(function(u) { return parseInt(u.is_admin) === 1; }).length;
    var userCount = filtered.length - adminCount;
    document.getElementById('userCount').textContent = 'Toplam: ' + filtered.length + ' kullanıcı (' + adminCount + ' antrenör, ' + userCount + ' kullanıcı)';
}

function filterUsers() { renderUsers(); }

function openUserDetail(userId) {
    apiGet('manager_get_user', { user_id: userId }).then(function(r) {
        if (!r.success) { showMsg(r.error || 'Kullanıcı bulunamadı.', false); return; }
        var u = r.user;
        document.getElementById('ueId').value = u.id;
        document.getElementById('ueUsername').value = u.username || '';
        document.getElementById('ueEmail').value = u.email || '';
        document.getElementById('ueFullName').value = u.full_name || '';
        document.getElementById('uePhone').value = u.phone || '';
        document.getElementById('ueAge').value = u.age || '';
        document.getElementById('ueGender').value = u.gender || '';
        document.getElementById('ueAddress').value = u.address || '';
        document.getElementById('ueIsAdmin').value = u.is_admin;
        document.getElementById('ueIsActive').value = u.is_active;
        document.getElementById('ueNewPassword').value = '';

        var sel = document.getElementById('ueTrainerId');
        sel.innerHTML = '<option value="">Antrenör yok</option>';
        for (var i = 0; i < trainerList.length; i++) {
            var t = trainerList[i];
            var selected = parseInt(u.trainer_id) === parseInt(t.id) ? ' selected' : '';
            sel.innerHTML += '<option value="' + t.id + '"' + selected + '>' + esc(t.full_name || t.username) + '</option>';
        }

        var passHash = u.password || '';
        var created = u.created_at ? u.created_at.substring(0, 16).replace('T', ' ') : '-';
        var lastLogin = u.last_login ? u.last_login.substring(0, 16).replace('T', ' ') : 'Hiç giriş yapmadı';
        document.getElementById('ueExtraInfo').innerHTML =
            '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">' +
            '<div>Kayıt Tarihi: <span style="color:#e2e8f0">' + created + '</span></div>' +
            '<div>Son Giriş: <span style="color:#e2e8f0">' + lastLogin + '</span></div>' +
            '<div style="grid-column:1/-1">Şifre Hash: <span style="color:#a78bfa;font-family:monospace;font-size:.72rem;word-break:break-all">' + esc(passHash) + '</span></div>' +
            '</div>';

        document.getElementById('userModalTitle').textContent = (u.full_name || u.username) + ' - Kullanıcı Detay';
        document.getElementById('userModal').classList.add('show');
    });
}

function closeUserModal() { document.getElementById('userModal').classList.remove('show'); }

document.getElementById('userModal').addEventListener('click', function(e) { if (e.target === this) closeUserModal(); });
document.getElementById('createUserModal').addEventListener('click', function(e) { if (e.target === this) closeCreateModal(); });

document.getElementById('userEditForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var userId = document.getElementById('ueId').value;
    var payload = {
        user_id: userId,
        username: document.getElementById('ueUsername').value.trim(),
        email: document.getElementById('ueEmail').value.trim(),
        full_name: document.getElementById('ueFullName').value.trim(),
        phone: document.getElementById('uePhone').value.trim(),
        age: document.getElementById('ueAge').value,
        gender: document.getElementById('ueGender').value,
        address: document.getElementById('ueAddress').value.trim(),
        is_admin: document.getElementById('ueIsAdmin').value,
        is_active: document.getElementById('ueIsActive').value,
        trainer_id: document.getElementById('ueTrainerId').value
    };
    var newPass = document.getElementById('ueNewPassword').value;
    if (newPass) payload.new_password = newPass;

    apiPost('manager_update_user', payload).then(function(r) {
        if (r.success) {
            showMsg('Kullanıcı başarıyla güncellendi.', true);
            closeUserModal();
            loadAllUsers();
        } else {
            showMsg(r.error || 'Güncelleme hatası.', false);
        }
    });
});

function deleteUser() {
    var userId = document.getElementById('ueId').value;
    var username = document.getElementById('ueUsername').value;
    if (!confirm(username + ' kullanıcısını silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')) return;
    apiPost('manager_delete_user', { user_id: userId }).then(function(r) {
        if (r.success) {
            showMsg('Kullanıcı silindi.', true);
            closeUserModal();
            loadAllUsers();
        } else {
            showMsg(r.error || 'Silme hatası.', false);
        }
    });
}

function openCreateUser() {
    document.getElementById('createUserForm').reset();
    document.getElementById('createUserModal').classList.add('show');
}

function closeCreateModal() { document.getElementById('createUserModal').classList.remove('show'); }

document.getElementById('createUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var payload = {
        username: document.getElementById('cuUsername').value.trim(),
        email: document.getElementById('cuEmail').value.trim(),
        full_name: document.getElementById('cuFullName').value.trim(),
        password: document.getElementById('cuPassword').value,
        is_admin: document.getElementById('cuIsAdmin').value
    };
    apiPost('manager_create_user', payload).then(function(r) {
        if (r.success) {
            showMsg('Kullanıcı başarıyla oluşturuldu.', true);
            closeCreateModal();
            loadAllUsers();
            loadTrainerList();
        } else {
            showMsg(r.error || 'Oluşturma hatası.', false);
        }
    });
});

loadAllUsers();
loadTrainerList();

function showMsg(t, ok) {
    var b = document.getElementById('msg');
    b.className = 'msg ' + (ok ? 'ok' : 'err');
    b.textContent = t;
    window.scrollTo(0, 0);
    setTimeout(function() { b.className = 'msg'; }, 4000);
}

var groups = {
    homepage: ['hero_title','hero_subtitle','feature_1_icon','feature_1_title','feature_1_desc','feature_2_icon','feature_2_title','feature_2_desc','feature_3_icon','feature_3_title','feature_3_desc','feature_4_icon','feature_4_title','feature_4_desc'],
    stats: ['stat_1_value','stat_1_label','stat_2_value','stat_2_label','stat_3_value','stat_3_label','cta_title','cta_subtitle'],
    contact: ['contact_address','contact_phone','contact_email','contact_hours'],
    email: ['smtp_host','smtp_port','smtp_user','smtp_pass','smtp_from_email','smtp_from_name'],
    security: ['manager_password']
};

function saveGroup(group) {
    var keys = groups[group];
    var data = {};
    for (var i = 0; i < keys.length; i++) {
        var el = document.getElementById(keys[i]);
        data[keys[i]] = el ? el.value : '';
    }
    var fd = new FormData();
    fd.append('action', 'save_settings');
    fd.append('settings', JSON.stringify(data));
    fetch('api.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) showMsg('Ayarlar başarıyla kaydedildi.', true);
            else showMsg(res.error || 'Kaydetme hatası.', false);
        })
        .catch(function() { showMsg('Sunucu hatası.', false); });
}

function testEmail() {
    var fd = new FormData();
    fd.append('action', 'test_email');
    document.getElementById('emailTestResult').textContent = 'Gönderiliyor...';
    fetch('api.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            document.getElementById('emailTestResult').textContent = res.success ? 'Test email gönderildi!' : ('Hata: ' + (res.error || 'Bilinmeyen'));
            document.getElementById('emailTestResult').style.color = res.success ? '#86efac' : '#fca5a5';
        })
        .catch(function() { document.getElementById('emailTestResult').textContent = 'Sunucu hatası.'; });
}
</script>
</body>
</html>
