<?php
require_once 'session-check.php';
requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Paneli | FİTNESS101</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body{background:#050505;color:#f0f0f0}
        .shell{max-width:1200px;margin:0 auto;padding:24px}
        .top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px}
        .top h1{margin:0;font-size:1.5rem}
        .top p{margin:4px 0 0;color:#9ca3af}
        .actions a{display:inline-block;padding:8px 12px;border-radius:8px;text-decoration:none;font-weight:600;font-size:.84rem}
        .actions .out{background:#7f1d1d;color:#fecaca}
        .layout{display:grid;grid-template-columns:230px 1fr;gap:16px}
        .side{background:#0d0d0d;border:1px solid #1a1a1a;border-radius:14px;padding:14px;height:fit-content;position:sticky;top:16px}
        .side a{display:block;padding:10px 12px;border-radius:8px;color:#9ca3af;text-decoration:none;font-weight:600;font-size:.86rem;margin-bottom:6px}
        .side a.active,.side a:hover{background:rgba(59,130,246,.12);color:#93c5fd}
        .panel{background:#0d0d0d;border:1px solid #1a1a1a;border-radius:14px;padding:16px;min-height:560px}
        .view{display:none}
        .view.active{display:block}
        .grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
        .stat{background:#080808;border:1px solid #1a1a1a;border-radius:12px;padding:14px}
        .stat .num{font-size:1.6rem;font-weight:800;color:#93c5fd}
        .stat .lbl{font-size:.75rem;color:#9ca3af;text-transform:uppercase}
        .card{background:#080808;border:1px solid #1a1a1a;border-radius:12px;padding:14px;margin-top:14px}
        .card h2{margin:0 0 10px;font-size:1rem;color:#3b82f6}
        .card h3{margin:0 0 6px;font-size:.95rem}
        .program-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
        .program{border:1px solid #1f1f1f;border-radius:10px;padding:12px;background:#060606}
        .program p{color:#9ca3af;font-size:.86rem;min-height:42px}
        .tag{display:inline-block;padding:3px 8px;border-radius:14px;background:rgba(59,130,246,.15);color:#93c5fd;font-size:.74rem;margin-bottom:6px}
        .btn{border:none;border-radius:8px;padding:8px 11px;font-weight:700;cursor:pointer;font-size:.82rem}
        .btn.primary{background:#3b82f6;color:#fff}
        .btn.ghost{background:#111827;border:1px solid #1f2937;color:#cbd5e1}
        .btn.ok{background:rgba(34,197,94,.2);color:#86efac;border:1px solid rgba(34,197,94,.3)}
        .table{width:100%;border-collapse:collapse}
        .table th,.table td{padding:9px;border-bottom:1px solid #1a1a1a;text-align:left;font-size:.84rem}
        .table th{font-size:.74rem;color:#9ca3af;text-transform:uppercase}
        .chip{display:inline-block;padding:2px 7px;border:1px solid #334155;color:#93c5fd;border-radius:12px;font-size:.74rem;margin-right:3px}
        .msg{display:none;padding:9px 12px;border-radius:8px;margin-bottom:10px;font-size:.84rem}
        .msg.ok{display:block;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:#86efac}
        .msg.err{display:block;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5}
        .modal{position:fixed;inset:0;background:rgba(0,0,0,.76);display:none;align-items:center;justify-content:center;z-index:9999}
        .modal.show{display:flex}
        .modal-body{background:#0b0b0b;border:1px solid #1f1f1f;border-radius:12px;width:min(760px,92vw);max-height:88vh;overflow:auto;padding:16px}
        .modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
        .close{border:none;background:#1f2937;color:#cbd5e1;padding:6px 10px;border-radius:6px;cursor:pointer}
        .exercise{border:1px solid #1f1f1f;border-radius:10px;background:#070707;margin-top:8px;overflow:hidden}
        .exercise button{width:100%;text-align:left;border:none;background:#0f172a;color:#bfdbfe;padding:10px;font-weight:700;cursor:pointer}
        .exercise .detail{display:none;padding:10px;color:#d1d5db;font-size:.88rem}
        .exercise .detail iframe{margin-top:8px;width:100%;aspect-ratio:16/9;border:none;border-radius:8px}
        .days{display:flex;gap:8px;flex-wrap:wrap}
        .days label{font-size:.8rem;color:#9ca3af;background:#090909;border:1px solid #1f1f1f;padding:5px 8px;border-radius:8px}
        input,select{padding:9px;border:1px solid #1f1f1f;border-radius:8px;background:#050505;color:#f0f0f0}
        @media(max-width:900px){.layout{grid-template-columns:1fr}.grid3,.program-grid{grid-template-columns:1fr}.side{position:static}}
    </style>
</head>
<body>
<nav class="navbar">
    <div class="container">
        <div class="logo">FİTNESS101</div>
        <ul class="nav-links">
            <li><a href="index.php">Anasayfa</a></li>
            <li><a href="user-dashboard.php" class="active">Panelim</a></li>
            <li><a href="programs.html">Programlar</a></li>
            <li><a href="contact.php">İletişim</a></li>
        </ul>
    </div>
</nav>

<div class="shell">
    <div class="top">
        <div>
            <h1>Hoş geldin, <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></h1>
            <p>Programını seç, günlerini belirle, seriyi günlük takip et.</p>
        </div>
        <div class="actions">
            <?php if (!empty($user['is_admin'])): ?><a class="btn ghost" style="text-decoration:none" href="admin.php">Admin</a><a style="display:inline-block;padding:8px 12px;border-radius:8px;text-decoration:none;font-weight:600;font-size:.84rem;background:#7c3aed;color:#e9d5ff" href="manager-login.php">Yönetici</a><?php endif; ?>
            <a class="out" href="logout.php">Çıkış</a>
        </div>
    </div>

    <div id="msg" class="msg"></div>

    <div class="layout">
        <aside class="side">
            <a href="#" class="active" data-view="overview">Özet</a>
            <a href="#" data-view="daily">Günlük</a>
            <a href="#" data-view="market">Program Seç</a>
            <a href="#" data-view="mine">Programlarım</a>
            <a href="#" data-view="profile">Profilim</a>
        </aside>

        <main class="panel">
            <section id="overview" class="view active">
                <div class="grid3">
                    <div class="stat"><div class="num" id="stActive">-</div><div class="lbl">Aktif Program</div></div>
                    <div class="stat"><div class="num" id="stMonth">-</div><div class="lbl">Bu Ay Yapılan</div></div>
                    <div class="stat"><div class="num" id="stLast">-</div><div class="lbl">Son Antrenman</div></div>
                </div>
                <div class="card">
                    <h2>Son Antrenmanlar</h2>
                    <table class="table">
                        <thead><tr><th>Tarih</th><th>Program</th><th>Süre</th><th>Kalori</th><th>Not</th></tr></thead>
                        <tbody id="recentRows"><tr><td colspan="5">Yükleniyor...</td></tr></tbody>
                    </table>
                </div>
            </section>

            <section id="market" class="view">
                <div class="card">
                    <h2>Program Havuzu</h2>
                    <div id="marketGrid" class="program-grid"></div>
                </div>
            </section>

            <section id="mine" class="view">
                <div class="card">
                    <h2>Programlarım ve Seri Takibi</h2>
                    <div id="myPrograms"></div>
                </div>
            </section>

            <section id="daily" class="view">
                <div class="card" style="margin-bottom:14px">
                    <h2>Günlük Kayıt</h2>
                    <form id="dailyForm" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
                        <div><label style="font-size:.75rem;color:#9ca3af">Tarih</label><input type="date" id="dlDate" style="width:100%"></div>
                        <div><label style="font-size:.75rem;color:#9ca3af">Kilo (kg) *</label><input type="number" step="0.1" id="dlWeight" placeholder="75.5" style="width:100%" required></div>
                        <div><label style="font-size:.75rem;color:#9ca3af">Boy (cm) *</label><input type="number" step="0.1" id="dlHeight" placeholder="175" style="width:100%"></div>
                        <div><label style="font-size:.75rem;color:#9ca3af">Alınan Kalori</label><input type="number" id="dlCalories" placeholder="2000" style="width:100%"></div>
                        <div><label style="font-size:.75rem;color:#9ca3af">Bel (cm)</label><input type="number" step="0.1" id="dlWaist" placeholder="Opsiyonel" style="width:100%"></div>
                        <div><label style="font-size:.75rem;color:#9ca3af">Boyun (cm)</label><input type="number" step="0.1" id="dlNeck" placeholder="Opsiyonel" style="width:100%"></div>
                        <div><label style="font-size:.75rem;color:#9ca3af">Kalça (cm)</label><input type="number" step="0.1" id="dlHip" placeholder="Opsiyonel" style="width:100%"></div>
                        <div><label style="font-size:.75rem;color:#9ca3af">Göğüs (cm)</label><input type="number" step="0.1" id="dlChest" placeholder="Opsiyonel" style="width:100%"></div>
                        <div><label style="font-size:.75rem;color:#9ca3af">Kol (cm)</label><input type="number" step="0.1" id="dlArm" placeholder="Opsiyonel" style="width:100%"></div>
                        <div><label style="font-size:.75rem;color:#9ca3af">Omuz (cm)</label><input type="number" step="0.1" id="dlShoulder" placeholder="Opsiyonel" style="width:100%"></div>
                        <div style="grid-column:1/-1"><label style="font-size:.75rem;color:#9ca3af">Not</label><input id="dlNotes" placeholder="Bugüne ait notlar..." style="width:100%"></div>
                        <div style="grid-column:1/-1"><button type="submit" class="btn primary">Kaydet</button></div>
                    </form>
                </div>

                <div class="card" style="margin-bottom:14px">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px">
                        <h2 style="margin:0">İstatistikler</h2>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <button class="btn ghost dlPeriod" data-period="week" style="padding:6px 10px;font-size:.78rem">Hafta</button>
                            <button class="btn primary dlPeriod" data-period="month" style="padding:6px 10px;font-size:.78rem">Ay</button>
                            <button class="btn ghost dlPeriod" data-period="3month" style="padding:6px 10px;font-size:.78rem">3 Ay</button>
                            <button class="btn ghost dlPeriod" data-period="year" style="padding:6px 10px;font-size:.78rem">Yıl</button>
                        </div>
                    </div>
                    <div id="dailyMetrics" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px"></div>
                    <div id="dailyChart" style="overflow-x:auto;background:#080808;border:1px solid #1a1a1a;border-radius:10px;padding:14px"></div>
                </div>

                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:10px">
                        <h2 style="margin:0">Günlük Geçmiş</h2>
                        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                            <input type="date" id="dlFilterFrom" style="padding:6px 8px;font-size:.82rem;width:auto">
                            <span style="color:#6b7280">-</span>
                            <input type="date" id="dlFilterTo" style="padding:6px 8px;font-size:.82rem;width:auto">
                            <button class="btn ghost" style="padding:6px 10px;font-size:.78rem" onclick="loadDailyLogs()">Filtrele</button>
                        </div>
                    </div>
                    <div style="overflow-x:auto">
                        <table class="table">
                            <thead><tr><th>Tarih</th><th>Kilo</th><th>Kalori</th><th>BMI</th><th>Yağ%</th><th>Antrenman</th><th></th></tr></thead>
                            <tbody id="dailyRows"><tr><td colspan="7">Yükleniyor...</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="profile" class="view">
                <div class="card" style="margin-bottom:14px">
                    <h2>Antrenörüm</h2>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                        <div id="trainerInfo" style="color:#93c5fd;font-weight:700;font-size:1.05rem">Yükleniyor...</div>
                    </div>
                    <div style="margin-top:10px;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <select id="pfTrainer" style="flex:1;min-width:180px"></select>
                        <button class="btn primary" onclick="changeTrainer()">Antrenör Değiştir</button>
                    </div>
                    <small style="color:#6b7280;margin-top:6px;display:block">Antrenör değiştirdiğinizde, yeni antrenörünüzün programlarını göreceksiniz.</small>
                </div>
                <div class="card">
                    <h2>Profil Güncelle</h2>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                        <input id="pfName" placeholder="Ad Soyad">
                        <input id="pfPhone" placeholder="Telefon">
                        <input id="pfAge" type="number" min="10" max="120" placeholder="Yaş">
                        <select id="pfGender"><option value="">Cinsiyet</option><option value="Erkek">Erkek</option><option value="Kadın">Kadın</option><option value="Diğer">Diğer</option></select>
                    </div>
                    <input id="pfAddress" style="width:100%;margin-top:8px" placeholder="Adres">
                    <button class="btn primary" style="margin-top:10px" onclick="saveProfile()">Kaydet</button>
                </div>
            </section>
        </main>
    </div>
</div>

<div id="planModal" class="modal">
    <div class="modal-body">
        <div class="modal-head"><h3>Programa Kayıt Ol</h3><button class="close" onclick="closePlan()">Kapat</button></div>
        <div id="planText" style="color:#9ca3af;font-size:.9rem"></div>
        <div style="margin-top:8px"><label style="color:#9ca3af;font-size:.8rem">Başlangıç Tarihi</label><br><input type="date" id="startDate"></div>
        <div style="margin-top:8px"><label style="color:#9ca3af;font-size:.8rem">Spor Günleri</label>
            <div class="days">
                <label><input type="checkbox" value="1"> Pzt</label><label><input type="checkbox" value="2"> Sal</label><label><input type="checkbox" value="3"> Çar</label><label><input type="checkbox" value="4"> Per</label><label><input type="checkbox" value="5"> Cum</label><label><input type="checkbox" value="6"> Cmt</label><label><input type="checkbox" value="7"> Paz</label>
            </div>
        </div>
        <button class="btn primary" style="margin-top:10px" onclick="submitAssign()">Programı Başlat</button>
    </div>
</div>

<div id="detailModal" class="modal">
    <div class="modal-body">
        <div class="modal-head"><h3 id="dTitle">Detay</h3><button class="close" onclick="closeDetail()">Kapat</button></div>
        <div id="dBody"></div>
    </div>
</div>

<div id="logDetailModal" class="modal">
    <div class="modal-body" style="max-width:540px">
        <div class="modal-head"><h3>Günlük Detay</h3><button class="close" onclick="closeLogDetail()">Kapat</button></div>
        <div id="logDetailBody"></div>
    </div>
</div>

<script>
var selectedProgram = null;
var programCache = {};

function apiGet(action, extra) {
    var q = new URLSearchParams({action: action});
    if (extra) {
        var keys = Object.keys(extra);
        for (var i = 0; i < keys.length; i++) {
            q.append(keys[i], extra[keys[i]]);
        }
    }
    return fetch('api.php?' + q.toString(), {credentials: 'same-origin'})
        .then(function(r) { return r.json(); })
        .catch(function(err) { console.error('API GET hata:', action, err); return {success: false}; });
}

function apiPost(action, payload) {
    var fd = new FormData();
    fd.append('action', action);
    if (payload) {
        var keys = Object.keys(payload);
        for (var i = 0; i < keys.length; i++) {
            fd.append(keys[i], payload[keys[i]]);
        }
    }
    return fetch('api.php', {method: 'POST', body: fd, credentials: 'same-origin'})
        .then(function(r) { return r.json(); })
        .catch(function(err) { console.error('API POST hata:', action, err); return {success: false}; });
}

function msg(t, ok) {
    var b = document.getElementById('msg');
    b.className = 'msg ' + (ok ? 'ok' : 'err');
    b.textContent = t;
    setTimeout(function() { b.className = 'msg'; }, 4000);
}

function fDate(v) { return (!v || v === '-') ? '-' : v.substring(0, 10); }

function dLabel(p) {
    if (p.duration_type === 'haftalik') return 'Haftalık';
    if (p.duration_type === 'aylik') return 'Aylık';
    if (p.duration_type === 'uc_aylik') return '3 Aylık';
    return 'Özel (' + (p.custom_days || 0) + ' gün)';
}

function dChips(csv) {
    var map = {1:'Pzt',2:'Sal',3:'Çar',4:'Per',5:'Cum',6:'Cmt',7:'Paz'};
    return (csv || '').split(',').filter(Boolean).map(function(x) {
        return '<span class="chip">' + (map[parseInt(x,10)] || x) + '</span>';
    }).join('');
}

function yt(url) {
    if (!url) return '';
    var vid = '';
    if (url.indexOf('watch?v=') !== -1) {
        vid = url.split('watch?v=')[1].split('&')[0];
    } else if (url.indexOf('youtu.be/') !== -1) {
        vid = url.split('youtu.be/')[1].split('?')[0];
    } else if (url.indexOf('/embed/') !== -1) {
        vid = url.split('/embed/')[1].split('?')[0];
    }
    if (vid) return 'https://www.youtube-nocookie.com/embed/' + vid;
    return url;
}

function ytIframe(url) {
    var src = yt(url);
    if (!src) return '';
    return '<iframe src="' + src + '" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" loading="lazy"></iframe>';
}

function esc(str) {
    var d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

/* ---- Sidebar Navigation ---- */
var sideLinks = document.querySelectorAll('.side a');
var allViews = document.querySelectorAll('.view');
for (var si = 0; si < sideLinks.length; si++) {
    sideLinks[si].addEventListener('click', function(e) {
        e.preventDefault();
        for (var j = 0; j < sideLinks.length; j++) sideLinks[j].classList.remove('active');
        this.classList.add('active');
        var viewId = this.getAttribute('data-view');
        for (var k = 0; k < allViews.length; k++) allViews[k].classList.remove('active');
        var target = document.getElementById(viewId);
        if (target) target.classList.add('active');
        if (viewId === 'overview') loadOverview();
        if (viewId === 'daily') loadDaily();
        if (viewId === 'market') loadMarket();
        if (viewId === 'mine') loadMine();
        if (viewId === 'profile') loadProfile();
    });
}

/* ---- Overview ---- */
function loadOverview() {
    apiGet('get_stats').then(function(r) {
        if (!r.success) return;
        document.getElementById('stActive').textContent = r.active_programs;
        document.getElementById('stMonth').textContent = r.month_workouts;
        document.getElementById('stLast').textContent = fDate(r.last_workout);
    });
    apiGet('get_workouts').then(function(r) {
        var rows = (r.workouts || []).slice(0, 8);
        var tb = document.getElementById('recentRows');
        if (rows.length === 0) {
            tb.innerHTML = '<tr><td colspan="5">Henüz antrenman yok.</td></tr>';
            return;
        }
        var html = '';
        for (var i = 0; i < rows.length; i++) {
            var x = rows[i];
            html += '<tr><td>' + fDate(x.workout_date) + '</td><td>' + esc(x.program_name) +
                '</td><td>' + x.duration_min + ' dk</td><td>' + x.calories +
                '</td><td>' + esc(x.notes || '-') + '</td></tr>';
        }
        tb.innerHTML = html;
    });
}

/* ---- Program Market ---- */
function loadMarket() {
    apiGet('list_program_templates').then(function(r) {
        var list = r.programs || [];
        var g = document.getElementById('marketGrid');
        if (list.length === 0) {
            g.innerHTML = '<div style="color:#9ca3af">Admin henüz program eklememiş.</div>';
            return;
        }
        var html = '';
        for (var i = 0; i < list.length; i++) {
            var p = list[i];
            programCache[p.id] = p;
            var trainerLabel = p.trainer_name || '';
            html += '<div class="program">' +
                '<h3>' + esc(p.name) + '</h3>' +
                '<span class="tag">' + dLabel(p) + '</span>' +
                (trainerLabel ? '<div style="color:#60a5fa;font-size:.78rem;margin:4px 0">Antrenör: ' + esc(trainerLabel) + '</div>' : '') +
                '<p>' + esc(p.description || '-') + '</p>' +
                '<button class="btn primary" data-action="select-program" data-pid="' + p.id + '">Programı Seç</button> ' +
                '<button class="btn ghost" data-action="view-exercises" data-pid="' + p.id + '">Hareketleri Gör</button>' +
                '</div>';
        }
        g.innerHTML = html;
    });
}

/* ---- Event Delegation for Market ---- */
document.getElementById('marketGrid').addEventListener('click', function(e) {
    var btn = e.target.closest('button');
    if (!btn) return;
    var action = btn.getAttribute('data-action');
    var pid = btn.getAttribute('data-pid');
    if (!action || !pid) return;
    var prog = programCache[pid];
    if (!prog) return;
    if (action === 'select-program') openPlan(prog);
    if (action === 'view-exercises') openExercises(pid, prog.name);
});

function openPlan(p) {
    selectedProgram = p;
    document.getElementById('planText').textContent = p.name + ' - ' + dLabel(p);
    document.getElementById('startDate').valueAsDate = new Date();
    var checks = document.querySelectorAll('#planModal input[type=checkbox]');
    for (var i = 0; i < checks.length; i++) checks[i].checked = false;
    document.getElementById('planModal').classList.add('show');
}

function closePlan() {
    selectedProgram = null;
    document.getElementById('planModal').classList.remove('show');
}

function submitAssign() {
    if (!selectedProgram) return;
    var start = document.getElementById('startDate').value;
    var checkedBoxes = document.querySelectorAll('#planModal input[type=checkbox]:checked');
    var days = [];
    for (var i = 0; i < checkedBoxes.length; i++) days.push(checkedBoxes[i].value);
    if (!start) { msg('Başlangıç tarihi seçin.', false); return; }
    if (days.length === 0) { msg('En az bir gün seçin.', false); return; }
    apiPost('assign_program', {
        program_id: selectedProgram.id,
        start_date: start,
        selected_days: days.join(',')
    }).then(function(r) {
        if (r.success) { msg('Program atandı.', true); closePlan(); loadMine(); }
        else msg(r.error || 'Atama hatası.', false);
    });
}

/* ---- My Programs ---- */
function loadMine() {
    apiGet('my_assignments').then(function(r) {
        var list = r.assignments || [];
        var root = document.getElementById('myPrograms');
        if (list.length === 0) {
            root.innerHTML = '<div style="color:#9ca3af">Henüz programa kayıt olmadınız. "Program Seç" sekmesinden başlayabilirsiniz.</div>';
            return;
        }
        var html = '';
        for (var i = 0; i < list.length; i++) {
            var a = list[i];
            html += '<div class="program" style="margin-bottom:8px">' +
                '<h3>' + esc(a.name) + '</h3>' +
                '<p style="min-height:auto">Başlangıç: ' + a.start_date + ' | Bitiş: ' + a.end_date + ' | Durum: ' + a.status + '</p>' +
                '<div>' + dChips(a.selected_days) + '</div>' +
                '<div style="margin-top:8px"><button class="btn primary" data-action="open-assignment" data-aid="' + a.id + '" data-aname="' + esc(a.name) + '">Seriyi Gör ve Takip Et</button></div>' +
                '</div>';
        }
        root.innerHTML = html;
    });
}

/* ---- Event Delegation for My Programs ---- */
document.getElementById('myPrograms').addEventListener('click', function(e) {
    var btn = e.target.closest('button');
    if (!btn) return;
    var action = btn.getAttribute('data-action');
    if (action === 'open-assignment') {
        var aid = btn.getAttribute('data-aid');
        var aname = btn.getAttribute('data-aname');
        openAssignment(aid, aname);
    }
});

/* ---- Exercises & Assignment Detail ---- */
function openExercises(pid, name) {
    apiGet('get_program_exercises', {program_id: pid}).then(function(r) {
        var list = r.exercises || [];
        document.getElementById('dTitle').textContent = name + ' - Hareketler';
        if (list.length === 0) {
            document.getElementById('dBody').innerHTML = '<div style="color:#9ca3af">Hareket yok.</div>';
        } else {
            var html = '';
            for (var i = 0; i < list.length; i++) {
                var ex = list[i];
                html += '<div class="exercise">' +
                    '<button data-toggle="ex-' + i + '">' + (i + 1) + '. ' + esc(ex.title) + '</button>' +
                    '<div class="detail" id="ex-' + i + '"><div>' + esc(ex.description || '-') + '</div>' +
                    (ex.youtube_url ? ytIframe(ex.youtube_url) : '') +
                    '</div></div>';
            }
            document.getElementById('dBody').innerHTML = html;
        }
        document.getElementById('detailModal').classList.add('show');
    });
}

function openAssignment(aid, name) {
    apiGet('assignment_schedule', {assignment_id: aid}).then(function(r) {
        if (!r.success) { msg(r.error || 'Detay açılamadı.', false); return; }
        var s = r.schedule || [];
        var ex = r.exercises || [];

        var tableHtml = '<h3>Günlük Seri Takibi</h3><table class="table"><thead><tr><th>Tarih</th><th>Durum</th><th>İşlem</th></tr></thead><tbody>';
        for (var i = 0; i < s.length; i++) {
            var x = s[i];
            var done = parseInt(x.is_completed, 10) === 1;
            tableHtml += '<tr><td>' + x.scheduled_date + '</td>' +
                '<td>' + (done ? '<span style="color:#22c55e">Yapıldı</span>' : '<span style="color:#ef4444">Bekliyor</span>') + '</td>' +
                '<td><button class="btn ' + (done ? 'ghost' : 'ok') + '" data-toggle-done="' + x.id + '">' + (done ? 'Geri Al' : 'Yaptım') + '</button></td></tr>';
        }
        tableHtml += '</tbody></table>';

        var exHtml = '<div style="margin-top:10px"><h3>Hareketler</h3>';
        for (var j = 0; j < ex.length; j++) {
            var e = ex[j];
            exHtml += '<div class="exercise">' +
                '<button data-toggle="aex-' + j + '">' + (j + 1) + '. ' + esc(e.title) + '</button>' +
                '<div class="detail" id="aex-' + j + '"><div>' + esc(e.description || '-') + '</div>' +
                (e.youtube_url ? ytIframe(e.youtube_url) : '') +
                '</div></div>';
        }
        exHtml += '</div>';

        document.getElementById('dTitle').textContent = name + ' - Seri';
        document.getElementById('dBody').innerHTML = tableHtml + exHtml;
        document.getElementById('detailModal').classList.add('show');
    });
}

/* ---- Event Delegation for Detail Modal ---- */
document.getElementById('dBody').addEventListener('click', function(e) {
    var btn = e.target.closest('button');
    if (!btn) return;

    var toggleId = btn.getAttribute('data-toggle');
    if (toggleId) {
        var el = document.getElementById(toggleId);
        if (el) el.style.display = (el.style.display === 'block') ? 'none' : 'block';
        return;
    }

    var doneId = btn.getAttribute('data-toggle-done');
    if (doneId) {
        toggleDone(doneId);
    }
});

function toggleDone(sid) {
    apiPost('toggle_schedule_done', {schedule_id: sid}).then(function(r) {
        if (r.success) {
            msg('Güncellendi.', true);
            document.getElementById('detailModal').classList.remove('show');
            loadMine();
            loadOverview();
        } else {
            msg(r.error || 'Hata', false);
        }
    });
}

function closeDetail() {
    document.getElementById('detailModal').classList.remove('show');
}

/* ---- Profile ---- */
function loadProfile() {
    apiGet('get_profile').then(function(r) {
        var p = r.profile || {};
        document.getElementById('pfName').value = p.full_name || '';
        document.getElementById('pfPhone').value = p.phone || '';
        document.getElementById('pfAge').value = p.age || '';
        document.getElementById('pfGender').value = p.gender || '';
        document.getElementById('pfAddress').value = p.address || '';

        var tInfo = document.getElementById('trainerInfo');
        if (p.trainer_name || p.trainer_username) {
            tInfo.textContent = p.trainer_name || p.trainer_username;
        } else {
            tInfo.textContent = 'Antrenör atanmamış';
            tInfo.style.color = '#ef4444';
        }

        loadTrainerDropdown(p.trainer_id);
    });
}

function loadTrainerDropdown(currentId) {
    apiGet('list_trainers').then(function(r) {
        var list = r.trainers || [];
        var sel = document.getElementById('pfTrainer');
        sel.innerHTML = '<option value="">Antrenör seçin...</option>';
        for (var i = 0; i < list.length; i++) {
            var t = list[i];
            var label = t.full_name || t.username;
            var selected = (parseInt(currentId, 10) === parseInt(t.id, 10)) ? ' selected' : '';
            sel.innerHTML += '<option value="' + t.id + '"' + selected + '>' + esc(label) + '</option>';
        }
    });
}

function changeTrainer() {
    var tid = document.getElementById('pfTrainer').value;
    if (!tid) { msg('Lütfen bir antrenör seçin.', false); return; }
    apiPost('change_trainer', { trainer_id: tid }).then(function(r) {
        if (r.success) {
            msg('Antrenörünüz değiştirildi.', true);
            loadProfile();
        } else {
            msg(r.error || 'Hata', false);
        }
    });
}

function saveProfile() {
    apiPost('update_profile', {
        full_name: document.getElementById('pfName').value,
        phone: document.getElementById('pfPhone').value,
        age: document.getElementById('pfAge').value,
        gender: document.getElementById('pfGender').value,
        address: document.getElementById('pfAddress').value
    }).then(function(r) {
        if (r.success) msg('Profil güncellendi.', true);
        else msg(r.error || 'Hata', false);
    });
}

/* ---- Daily Log ---- */
var dailyPeriod = 'month';
var allDailyLogs = [];

function localDateStr(d) {
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + day;
}

document.getElementById('dlDate').value = localDateStr(new Date());

(function() {
    var now = new Date();
    var threeMonthsAgo = new Date(now.getFullYear(), now.getMonth() - 3, 1);
    document.getElementById('dlFilterFrom').value = localDateStr(threeMonthsAgo);
    document.getElementById('dlFilterTo').value = localDateStr(now);
})();

function calcBMI(w, h) {
    if (!w || !h) return null;
    var hm = h / 100;
    return (w / (hm * hm)).toFixed(1);
}

function calcBodyFat(gender, waist, neck, hip, height) {
    if (!waist || !neck || !height) return null;
    if (waist <= neck) return null;
    if (gender === 'Kadın') {
        if (!hip) return null;
        var bf = 163.205 * Math.log10(waist + hip - neck) - 97.684 * Math.log10(height) - 78.387;
        return bf > 0 && bf < 60 ? bf.toFixed(1) : null;
    }
    var bf = 86.010 * Math.log10(waist - neck) - 70.041 * Math.log10(height) + 36.76;
    return bf > 0 && bf < 50 ? bf.toFixed(1) : null;
}

function bmiLabel(bmi) {
    if (!bmi) return '-';
    var v = parseFloat(bmi);
    if (v < 18.5) return bmi + ' (Zayıf)';
    if (v < 25) return bmi + ' (Normal)';
    if (v < 30) return bmi + ' (Fazla)';
    return bmi + ' (Obez)';
}

function bmiColor(bmi) {
    if (!bmi) return '#6b7280';
    var v = parseFloat(bmi);
    if (v < 18.5) return '#f59e0b';
    if (v < 25) return '#22c55e';
    if (v < 30) return '#f97316';
    return '#ef4444';
}

function formatDate(d) {
    if (!d) return '-';
    var parts = d.split('-');
    if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];
    return d;
}

document.getElementById('dailyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var payload = {
        log_date: document.getElementById('dlDate').value,
        weight: document.getElementById('dlWeight').value,
        height: document.getElementById('dlHeight').value,
        calories_in: document.getElementById('dlCalories').value || '0',
        waist: document.getElementById('dlWaist').value,
        neck: document.getElementById('dlNeck').value,
        hip: document.getElementById('dlHip').value,
        chest: document.getElementById('dlChest').value,
        arm: document.getElementById('dlArm').value,
        shoulder: document.getElementById('dlShoulder').value,
        notes: document.getElementById('dlNotes').value
    };
    apiPost('save_daily_log', payload).then(function(r) {
        if (r.success) {
            msg('Günlük kaydedildi.', true);
            document.getElementById('dlWeight').value = '';
            document.getElementById('dlCalories').value = '';
            document.getElementById('dlWaist').value = '';
            document.getElementById('dlNeck').value = '';
            document.getElementById('dlHip').value = '';
            document.getElementById('dlChest').value = '';
            document.getElementById('dlArm').value = '';
            document.getElementById('dlShoulder').value = '';
            document.getElementById('dlNotes').value = '';
            loadDailyLogs();
            loadDailyStats();
        } else {
            msg(r.error || 'Bir hata oluştu. Konsolu kontrol edin.', false);
            console.error('save_daily_log error:', r);
        }
    });
});

var periodBtns = document.querySelectorAll('.dlPeriod');
for (var pi = 0; pi < periodBtns.length; pi++) {
    periodBtns[pi].addEventListener('click', function() {
        dailyPeriod = this.getAttribute('data-period');
        for (var j = 0; j < periodBtns.length; j++) {
            periodBtns[j].className = 'btn ' + (periodBtns[j].getAttribute('data-period') === dailyPeriod ? 'primary' : 'ghost') + ' dlPeriod';
            periodBtns[j].style.padding = '6px 10px';
            periodBtns[j].style.fontSize = '.78rem';
        }
        loadDailyStats();
    });
}

function loadDaily() {
    loadDailyLogs();
    loadDailyStats();
}

function loadDailyLogs() {
    var from = document.getElementById('dlFilterFrom').value;
    var to = document.getElementById('dlFilterTo').value;
    if (!from || !to) {
        var now = new Date();
        from = from || localDateStr(new Date(now.getFullYear(), now.getMonth() - 3, 1));
        to = to || localDateStr(now);
    }
    apiGet('get_daily_logs', { from: from, to: to }).then(function(r) {
        var logs = r.logs || [];
        allDailyLogs = logs;
        var tb = document.getElementById('dailyRows');
        if (logs.length === 0) {
            tb.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:#6b7280">Bu tarih aralığında kayıt bulunamadı.</td></tr>';
            return;
        }
        var html = '';
        for (var i = 0; i < logs.length; i++) {
            var l = logs[i];
            var bmi = calcBMI(parseFloat(l.weight), parseFloat(l.height));
            var bf = calcBodyFat('<?= ($user && isset($user['gender'])) ? $user['gender'] : 'Erkek' ?>', parseFloat(l.waist), parseFloat(l.neck), parseFloat(l.hip), parseFloat(l.height));
            var wk = parseInt(l.workout_count, 10) || 0;
            html += '<tr style="cursor:pointer;transition:background .15s" onmouseover="this.style.background=\'#111827\'" onmouseout="this.style.background=\'\'" data-logidx="' + i + '">' +
                '<td style="font-weight:600;color:#93c5fd">' + formatDate(l.log_date) + '</td>' +
                '<td>' + (l.weight ? '<span style="font-weight:600">' + l.weight + '</span> kg' : '-') + '</td>' +
                '<td>' + (l.calories_in ? '<span style="color:#fbbf24">' + l.calories_in + '</span> kcal' : '-') + '</td>' +
                '<td style="color:' + bmiColor(bmi) + '">' + bmiLabel(bmi) + '</td>' +
                '<td>' + (bf ? '<span style="color:#a78bfa">' + bf + '%</span>' : '-') + '</td>' +
                '<td>' + (wk > 0 ? '<span style="color:#22c55e;font-weight:600">' + wk + ' antrenman</span>' : '<span style="color:#374151">-</span>') + '</td>' +
                '<td><button class="btn ghost" style="padding:3px 8px;font-size:.72rem" data-logdetail="' + i + '">Detay</button></td>' +
                '</tr>';
        }
        tb.innerHTML = html;

        if (logs.length > 0) {
            var latest = logs[0];
            if (latest.height) document.getElementById('dlHeight').value = latest.height;
        }
    });
}

document.getElementById('dailyRows').addEventListener('click', function(e) {
    var btn = e.target.closest('[data-logdetail]');
    var row = e.target.closest('[data-logidx]');
    var idx = btn ? parseInt(btn.getAttribute('data-logdetail'), 10) : (row ? parseInt(row.getAttribute('data-logidx'), 10) : -1);
    if (idx >= 0 && idx < allDailyLogs.length) openLogDetail(allDailyLogs[idx]);
});

function openLogDetail(l) {
    var gender = '<?= ($user && isset($user['gender'])) ? $user['gender'] : 'Erkek' ?>';
    var bmi = calcBMI(parseFloat(l.weight), parseFloat(l.height));
    var bf = calcBodyFat(gender, parseFloat(l.waist), parseFloat(l.neck), parseFloat(l.hip), parseFloat(l.height));
    var wk = parseInt(l.workout_count, 10) || 0;

    var html = '<div style="margin-bottom:14px;text-align:center">' +
        '<div style="font-size:1.4rem;font-weight:800;color:#93c5fd">' + formatDate(l.log_date) + '</div>' +
        '</div>';

    html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">';
    html += metricCard('Kilo', l.weight ? l.weight + ' kg' : '-', '#3b82f6');
    html += metricCard('Boy', l.height ? l.height + ' cm' : '-', '#6366f1');
    html += metricCard('Kalori Alımı', l.calories_in ? l.calories_in + ' kcal' : '-', '#fbbf24');
    html += metricCard('BMI', bmiLabel(bmi), bmiColor(bmi));
    if (bf) html += metricCard('Yağ Oranı', bf + '%', '#a78bfa');
    html += metricCard('Antrenman', wk > 0 ? wk + ' antrenman' : 'Yapılmadı', wk > 0 ? '#22c55e' : '#6b7280');
    html += '</div>';

    var hasMeasurements = l.waist || l.neck || l.hip || l.chest || l.arm || l.shoulder;
    if (hasMeasurements) {
        html += '<div style="background:#0f172a;border:1px solid #1e293b;border-radius:10px;padding:12px;margin-bottom:14px">';
        html += '<div style="font-weight:700;color:#e2e8f0;margin-bottom:8px;font-size:.85rem">Vücut Ölçüleri</div>';
        html += '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">';
        if (l.waist) html += measureItem('Bel', l.waist);
        if (l.neck) html += measureItem('Boyun', l.neck);
        if (l.hip) html += measureItem('Kalça', l.hip);
        if (l.chest) html += measureItem('Göğüs', l.chest);
        if (l.arm) html += measureItem('Kol', l.arm);
        if (l.shoulder) html += measureItem('Omuz', l.shoulder);
        html += '</div></div>';
    }

    if (l.notes) {
        html += '<div style="background:#0f172a;border:1px solid #1e293b;border-radius:10px;padding:12px;margin-bottom:14px">';
        html += '<div style="font-weight:700;color:#e2e8f0;margin-bottom:4px;font-size:.85rem">Not</div>';
        html += '<div style="color:#9ca3af;font-size:.88rem">' + esc(l.notes) + '</div>';
        html += '</div>';
    }

    html += '<div style="text-align:center;margin-top:10px">' +
        '<button class="btn ghost" style="color:#ef4444;border-color:#ef4444" onclick="deleteLog(' + l.id + ')">Kaydı Sil</button>' +
        '</div>';

    document.getElementById('logDetailBody').innerHTML = html;
    document.getElementById('logDetailModal').classList.add('show');
}

function metricCard(label, value, color) {
    return '<div style="background:#0f172a;border:1px solid #1e293b;border-radius:10px;padding:10px;text-align:center">' +
        '<div style="font-size:1.05rem;font-weight:700;color:' + color + '">' + value + '</div>' +
        '<div style="font-size:.72rem;color:#6b7280;margin-top:2px">' + label + '</div>' +
        '</div>';
}

function measureItem(label, val) {
    return '<div style="text-align:center">' +
        '<div style="font-size:.95rem;font-weight:600;color:#e2e8f0">' + val + ' <span style="font-size:.7rem;color:#6b7280">cm</span></div>' +
        '<div style="font-size:.68rem;color:#6b7280">' + label + '</div>' +
        '</div>';
}

function closeLogDetail() {
    document.getElementById('logDetailModal').classList.remove('show');
}

document.getElementById('logDetailModal').addEventListener('click', function(e) {
    if (e.target === this) closeLogDetail();
});

function deleteLog(logId) {
    if (!confirm('Bu günlük kaydını silmek istediğinize emin misiniz?')) return;
    apiPost('delete_daily_log', { log_id: logId }).then(function(r) {
        if (r.success) {
            msg('Kayıt silindi.', true);
            closeLogDetail();
            loadDailyLogs();
            loadDailyStats();
        } else {
            msg(r.error || 'Silinemedi.', false);
        }
    });
}

function loadDailyStats() {
    apiGet('get_daily_stats', { period: dailyPeriod }).then(function(r) {
        var logs = r.logs || [];
        var gender = r.gender || 'Erkek';
        var metrics = document.getElementById('dailyMetrics');
        var chart = document.getElementById('dailyChart');

        if (logs.length === 0) {
            metrics.innerHTML = '<div style="color:#6b7280;grid-column:1/-1;text-align:center;padding:16px">Bu dönemde veri yok.</div>';
            chart.innerHTML = '<div style="text-align:center;color:#374151;padding:20px">Grafik için veri bekleniyor...</div>';
            return;
        }

        var first = logs[0], last = logs[logs.length - 1];
        var weightDiff = (parseFloat(last.weight) - parseFloat(first.weight)).toFixed(1);
        var lastBmi = calcBMI(parseFloat(last.weight), parseFloat(last.height));
        var lastBf = calcBodyFat(gender, parseFloat(last.waist), parseFloat(last.neck), parseFloat(last.hip), parseFloat(last.height));
        var totalCal = 0, totalBurned = 0, workoutDays = 0;
        var minWeight = 9999, maxWeight = 0;
        for (var i = 0; i < logs.length; i++) {
            totalCal += parseInt(logs[i].calories_in, 10) || 0;
            totalBurned += parseInt(logs[i].calories_burned, 10) || 0;
            if (parseInt(logs[i].workout_count, 10) > 0) workoutDays++;
            var ww = parseFloat(logs[i].weight);
            if (ww < minWeight) minWeight = ww;
            if (ww > maxWeight) maxWeight = ww;
        }
        var avgCal = logs.length > 0 ? Math.round(totalCal / logs.length) : 0;
        var diffSign = weightDiff > 0 ? '+' : '';
        var diffColor = weightDiff > 0 ? '#ef4444' : (weightDiff < 0 ? '#22c55e' : '#6b7280');

        metrics.innerHTML =
            statCard(last.weight + ' kg', 'Son Kilo', '#3b82f6', 'linear-gradient(135deg,#1e3a5f 0%,#0f172a 100%)') +
            statCard(diffSign + weightDiff + ' kg', 'Kilo Değişimi', diffColor, 'linear-gradient(135deg,#1a1a2e 0%,#0f172a 100%)') +
            statCard(bmiLabel(lastBmi), 'Vücut Kitle İndeksi', bmiColor(lastBmi), 'linear-gradient(135deg,#162032 0%,#0f172a 100%)') +
            (lastBf ? statCard(lastBf + '%', 'Yağ Oranı', '#a78bfa', 'linear-gradient(135deg,#1f1635 0%,#0f172a 100%)') : '') +
            statCard(avgCal + ' kcal', 'Ortalama Kalori', '#fbbf24', 'linear-gradient(135deg,#2d2305 0%,#0f172a 100%)') +
            statCard(workoutDays + ' / ' + logs.length + ' gün', 'Antrenman Günleri', '#22c55e', 'linear-gradient(135deg,#0a2e1a 0%,#0f172a 100%)');

        var range = maxWeight - minWeight || 1;
        var chartH = 160;
        var svgW = Math.max(logs.length * 50, 400);
        var padL = 50, padR = 20, padT = 20, padB = 40;
        var plotW = svgW - padL - padR;
        var plotH = chartH - padT - padB;

        var svgHtml = '<svg width="' + svgW + '" height="' + chartH + '" style="display:block;min-width:100%">';
        svgHtml += '<defs><linearGradient id="lineGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#3b82f6" stop-opacity="0.4"/><stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/></linearGradient></defs>';

        var gridLines = 4;
        for (var g = 0; g <= gridLines; g++) {
            var gy = padT + (plotH / gridLines) * g;
            var gVal = (maxWeight - (range / gridLines) * g).toFixed(1);
            svgHtml += '<line x1="' + padL + '" y1="' + gy + '" x2="' + (svgW - padR) + '" y2="' + gy + '" stroke="#1e293b" stroke-width="1"/>';
            svgHtml += '<text x="' + (padL - 6) + '" y="' + (gy + 4) + '" fill="#6b7280" font-size="10" text-anchor="end">' + gVal + '</text>';
        }

        var areaPath = 'M';
        var linePath = 'M';
        var points = [];
        for (var k = 0; k < logs.length; k++) {
            var xPos = padL + (plotW / Math.max(logs.length - 1, 1)) * k;
            var yPos = padT + plotH - ((parseFloat(logs[k].weight) - minWeight) / range) * plotH;
            points.push({ x: xPos, y: yPos, log: logs[k] });
            if (k === 0) {
                areaPath += xPos + ',' + yPos;
                linePath += xPos + ',' + yPos;
            } else {
                areaPath += ' L' + xPos + ',' + yPos;
                linePath += ' L' + xPos + ',' + yPos;
            }
        }
        areaPath += ' L' + points[points.length - 1].x + ',' + (padT + plotH) + ' L' + points[0].x + ',' + (padT + plotH) + ' Z';
        svgHtml += '<path d="' + areaPath + '" fill="url(#lineGrad)"/>';
        svgHtml += '<path d="' + linePath + '" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>';

        for (var m = 0; m < points.length; m++) {
            var hasWorkout = parseInt(points[m].log.workout_count, 10) > 0;
            var pColor = hasWorkout ? '#22c55e' : '#3b82f6';
            svgHtml += '<circle cx="' + points[m].x + '" cy="' + points[m].y + '" r="4" fill="' + pColor + '" stroke="#0f172a" stroke-width="2"/>';
            var dateLabel = points[m].log.log_date.substring(5);
            svgHtml += '<text x="' + points[m].x + '" y="' + (padT + plotH + 16) + '" fill="#6b7280" font-size="9" text-anchor="middle">' + dateLabel + '</text>';
            svgHtml += '<text x="' + points[m].x + '" y="' + (points[m].y - 8) + '" fill="#9ca3af" font-size="9" text-anchor="middle">' + points[m].log.weight + '</text>';
        }

        svgHtml += '</svg>';

        var chartContent = '<div style="margin-bottom:8px;display:flex;justify-content:space-between;align-items:center">';
        chartContent += '<div style="font-weight:700;color:#e2e8f0;font-size:.88rem">Kilo Takibi</div>';
        chartContent += '<div style="font-size:.72rem;color:#6b7280">';
        chartContent += '<span style="display:inline-block;width:8px;height:8px;background:#3b82f6;border-radius:50%;margin-right:4px"></span>Kilo ';
        chartContent += '<span style="display:inline-block;width:8px;height:8px;background:#22c55e;border-radius:50%;margin-left:8px;margin-right:4px"></span>Antrenman günü';
        chartContent += '</div></div>';
        chartContent += '<div style="overflow-x:auto">' + svgHtml + '</div>';
        chart.innerHTML = chartContent;
    });
}

function statCard(value, label, color, bg) {
    return '<div style="background:' + bg + ';border:1px solid #1e293b;border-radius:12px;padding:14px;text-align:center;position:relative;overflow:hidden">' +
        '<div style="position:absolute;top:-10px;right:-10px;width:40px;height:40px;background:' + color + ';opacity:0.08;border-radius:50%"></div>' +
        '<div style="font-size:1.15rem;font-weight:800;color:' + color + ';line-height:1.2">' + value + '</div>' +
        '<div style="font-size:.7rem;color:#6b7280;margin-top:4px">' + label + '</div>' +
        '</div>';
}

/* ---- Init ---- */
var urlParams = new URLSearchParams(window.location.search);
var autoProgram = urlParams.get('program_id');

if (autoProgram) {
    for (var j = 0; j < sideLinks.length; j++) sideLinks[j].classList.remove('active');
    for (var k = 0; k < allViews.length; k++) allViews[k].classList.remove('active');
    var marketLink = document.querySelector('.side a[data-view="market"]');
    if (marketLink) marketLink.classList.add('active');
    var marketView = document.getElementById('market');
    if (marketView) marketView.classList.add('active');

    apiGet('list_program_templates').then(function(r) {
        var list = r.programs || [];
        var g = document.getElementById('marketGrid');
        var html = '';
        for (var i = 0; i < list.length; i++) {
            var p = list[i];
            programCache[p.id] = p;
            var trainerLabel = p.trainer_name || '';
            html += '<div class="program">' +
                '<h3>' + esc(p.name) + '</h3>' +
                '<span class="tag">' + dLabel(p) + '</span>' +
                (trainerLabel ? '<div style="color:#60a5fa;font-size:.78rem;margin:4px 0">Antrenör: ' + esc(trainerLabel) + '</div>' : '') +
                '<p>' + esc(p.description || '-') + '</p>' +
                '<button class="btn primary" data-action="select-program" data-pid="' + p.id + '">Programı Seç</button> ' +
                '<button class="btn ghost" data-action="view-exercises" data-pid="' + p.id + '">Hareketleri Gör</button>' +
                '</div>';
        }
        g.innerHTML = html;

        if (programCache[autoProgram]) {
            openPlan(programCache[autoProgram]);
        }
    });
} else {
    loadOverview();
}
</script>
</body>
</html>
