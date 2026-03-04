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
            <li><a href="index.html">Anasayfa</a></li>
            <li><a href="user-dashboard.php" class="active">Panelim</a></li>
            <li><a href="programs.html">Programlar</a></li>
            <li><a href="contact.html">İletişim</a></li>
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
            <?php if (!empty($user['is_admin'])): ?><a class="btn ghost" style="text-decoration:none" href="admin.php">Admin</a><?php endif; ?>
            <a class="out" href="logout.php">Çıkış</a>
        </div>
    </div>

    <div id="msg" class="msg"></div>

    <div class="layout">
        <aside class="side">
            <a href="#" class="active" data-view="overview">Özet</a>
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

            <section id="profile" class="view">
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
    if (url.indexOf('watch?v=') !== -1) {
        var id = url.split('watch?v=')[1].split('&')[0];
        return 'https://www.youtube.com/embed/' + id;
    }
    if (url.indexOf('youtu.be/') !== -1) {
        var id2 = url.split('youtu.be/')[1].split('?')[0];
        return 'https://www.youtube.com/embed/' + id2;
    }
    return url;
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
            html += '<div class="program">' +
                '<h3>' + esc(p.name) + '</h3>' +
                '<span class="tag">' + dLabel(p) + '</span>' +
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
                    (ex.youtube_url ? '<iframe src="' + yt(ex.youtube_url) + '" allowfullscreen></iframe>' : '') +
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
                (e.youtube_url ? '<iframe src="' + yt(e.youtube_url) + '" allowfullscreen></iframe>' : '') +
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

/* ---- Init ---- */
loadOverview();
</script>
</body>
</html>
