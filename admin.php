<?php
require_once 'session-check.php';
requireAdmin();
$u = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | FİTNESS101</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body{background:#050505;color:#f0f0f0}
        .wrap{max-width:1200px;margin:0 auto;padding:24px}
        .top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
        .top h1{margin:0;font-size:1.5rem}
        .top p{margin:0;color:#9ca3af}
        .actions a{display:inline-block;padding:8px 12px;border-radius:8px;text-decoration:none;font-weight:600;font-size:.85rem}
        .actions .back{background:#111827;border:1px solid #1f2937;color:#cbd5e1}
        .actions .out{background:#7f1d1d;color:#fecaca}
        .layout{display:grid;grid-template-columns:1.2fr 1fr;gap:16px}
        .card{background:#0d0d0d;border:1px solid #1a1a1a;border-radius:14px;padding:16px}
        .card h2{margin:0 0 12px;font-size:1rem;color:#3b82f6}
        label{display:block;color:#9ca3af;font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;margin:10px 0 6px}
        input,select,textarea{width:100%;padding:10px;border:1px solid #1f1f1f;border-radius:8px;background:#050505;color:#f0f0f0;font-family:inherit}
        textarea{min-height:70px;resize:vertical}
        .btn{margin-top:12px;padding:9px 12px;border:none;border-radius:8px;font-weight:700;cursor:pointer}
        .btn.primary{background:#3b82f6;color:#fff}
        .btn.secondary{background:#111827;color:#cbd5e1;border:1px solid #1f2937}
        .btn.danger{background:#7f1d1d;color:#fecaca}
        .exercise{border:1px solid #1f1f1f;border-radius:10px;padding:10px;background:#080808;margin-top:10px}
        .exercise-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px;border-bottom:1px solid #1a1a1a;text-align:left;font-size:.86rem}
        th{font-size:.74rem;text-transform:uppercase;color:#9ca3af}
        .tag{display:inline-block;padding:3px 8px;border-radius:14px;background:rgba(59,130,246,.15);color:#93c5fd;font-size:.74rem}
        .msg{display:none;padding:10px 12px;border-radius:8px;margin-bottom:10px;font-size:.84rem}
        .msg.ok{display:block;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:#86efac}
        .msg.err{display:block;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5}
        @media(max-width:900px){.layout{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <div>
            <h1>Admin Panel</h1>
            <p>Hoş geldin, <?= htmlspecialchars($u['full_name'] ?: $u['username']) ?></p>
        </div>
        <div class="actions">
            <a style="background:#7c3aed;color:#e9d5ff;border:none" href="manager-login.php">Yönetici Paneli</a>
            <a class="back" href="user-dashboard.php">Kullanıcı Paneli</a>
            <a class="out" href="logout.php">Çıkış</a>
        </div>
    </div>

    <div id="msg" class="msg"></div>

    <div class="layout">
        <div class="card">
            <h2>Program ve Hareket Ekle</h2>
            <form id="programForm">
                <label>Program Adı</label>
                <input id="pName" placeholder="Örn: Aylık Güç Programı" required>

                <label>Açıklama</label>
                <textarea id="pDescription" placeholder="Programın amacı, seviyesi, hedefi..."></textarea>

                <label>Süre</label>
                <select id="pDuration" onchange="onDurationChange()">
                    <option value="haftalik">Haftalık</option>
                    <option value="aylik">Aylık</option>
                    <option value="uc_aylik">3 Aylık</option>
                    <option value="ozel">Özel</option>
                </select>

                <div id="customDaysWrap" style="display:none;">
                    <label>Özel Gün Sayısı</label>
                    <input id="pCustomDays" type="number" min="1" placeholder="Örn: 45">
                </div>

                <label>Hareketler</label>
                <div id="exerciseList"></div>
                <button type="button" class="btn secondary" onclick="addExercise()">+ Hareket Ekle</button>
                <button type="submit" class="btn primary">Programı Kaydet</button>
            </form>
        </div>

        <div>
            <div class="card" style="margin-bottom:16px">
                <h2>Program Listesi</h2>
                <table>
                    <thead><tr><th>Program</th><th>Süre</th><th>Hareket</th><th>Tarih</th></tr></thead>
                    <tbody id="programRows"><tr><td colspan="4">Yükleniyor...</td></tr></tbody>
                </table>
            </div>

            <div class="card">
                <h2>Antrenör Listesi</h2>
                <p style="color:#6b7280;font-size:.82rem;margin-bottom:10px">Yeni antrenör eklemek için <a href="manager-login.php" style="color:#93c5fd">Yönetici Paneli</a>'ni kullanın.</p>
                <table>
                    <thead><tr><th>Ad Soyad</th><th>Kullanıcı Adı</th><th>Email</th><th>Öğrenci</th></tr></thead>
                    <tbody id="trainerRows"><tr><td colspan="4">Yükleniyor...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:16px">
        <h2>Öğrenci Takibi</h2>
        <table>
            <thead><tr><th>Ad Soyad</th><th>Kullanıcı</th><th>Son Kilo</th><th>Son Kayıt</th><th>Toplam Giriş</th><th>İşlem</th></tr></thead>
            <tbody id="studentRows"><tr><td colspan="6">Yükleniyor...</td></tr></tbody>
        </table>
    </div>

    <div id="studentDetail" class="card" style="margin-top:16px;display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
            <h2 id="sdTitle">Öğrenci Detay</h2>
            <button class="btn secondary" onclick="document.getElementById('studentDetail').style.display='none'">Kapat</button>
        </div>
        <div style="display:flex;gap:8px;margin-bottom:10px;flex-wrap:wrap">
            <input type="date" id="sdFrom" style="width:auto">
            <input type="date" id="sdTo" style="width:auto">
            <button class="btn primary" onclick="loadStudentLogs()">Filtrele</button>
        </div>
        <div style="overflow-x:auto">
            <table>
                <thead><tr><th>Tarih</th><th>Kilo</th><th>Boy</th><th>Kalori</th><th>Bel</th><th>BMI</th><th>Yağ%</th><th>Antrenman</th></tr></thead>
                <tbody id="sdRows"><tr><td colspan="8">Veri yok.</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showMsg(t, ok){
    var b=document.getElementById('msg');
    b.className='msg '+(ok?'ok':'err');
    b.textContent=t;
    setTimeout(function(){b.className='msg';},4000);
}
function onDurationChange(){
    document.getElementById('customDaysWrap').style.display =
        document.getElementById('pDuration').value === 'ozel' ? 'block' : 'none';
}
function renumberExercises(){
    document.querySelectorAll('#exerciseList .exercise').forEach(function(el,i){
        el.querySelector('.exercise-top strong').textContent='Hareket #'+(i+1);
    });
}
function addExercise(data){
    data=data||{};
    var list=document.getElementById('exerciseList');
    var idx=list.children.length+1;
    var div=document.createElement('div');
    div.className='exercise';
    div.innerHTML=''
        +'<div class="exercise-top"><strong>Hareket #'+idx+'</strong><button type="button" class="btn danger" onclick="this.closest(\'.exercise\').remove();renumberExercises()">Sil</button></div>'
        +'<label>Hareket İsmi</label><input class="ex-title" placeholder="Örn: Squat" value="'+(data.title||'')+'">'
        +'<label>Açıklama</label><textarea class="ex-desc" placeholder="Hareket nasıl yapılır?">'+(data.description||'')+'</textarea>'
        +'<label>YouTube Linki</label><input class="ex-url" placeholder="https://www.youtube.com/watch?v=..." value="'+(data.youtube_url||'')+'">';
    list.appendChild(div);
}
function collectExercises(){
    return Array.from(document.querySelectorAll('#exerciseList .exercise')).map(function(r){
        return {
            title: r.querySelector('.ex-title').value.trim(),
            description: r.querySelector('.ex-desc').value.trim(),
            youtube_url: r.querySelector('.ex-url').value.trim()
        };
    }).filter(function(x){ return x.title !== ''; });
}
function loadPrograms(){
    fetch('api.php?action=admin_list_programs')
        .then(function(r){ return r.json(); })
        .then(function(res){
            var rows=document.getElementById('programRows');
            var list=res.programs||[];
            if(!res.success||list.length===0){ rows.innerHTML='<tr><td colspan="4">Program yok.</td></tr>'; return; }
            rows.innerHTML=list.map(function(p){
                var sure=p.duration_type==='haftalik'?'Haftalık':p.duration_type==='aylik'?'Aylık':p.duration_type==='uc_aylik'?'3 Aylık':'Özel ('+(p.custom_days||0)+' gün)';
                return '<tr><td><strong>'+p.name+'</strong><br><small style="color:#6b7280">'+(p.description||'-')+'</small></td><td><span class="tag">'+sure+'</span></td><td>'+p.exercise_count+'</td><td>'+(p.created_at||'').substring(0,10)+'</td></tr>';
            }).join('');
        });
}
document.getElementById('programForm').addEventListener('submit', function(e){
    e.preventDefault();
    var fd=new FormData();
    fd.append('action','admin_create_program');
    fd.append('name',document.getElementById('pName').value.trim());
    fd.append('description',document.getElementById('pDescription').value.trim());
    fd.append('duration_type',document.getElementById('pDuration').value);
    fd.append('custom_days',document.getElementById('pCustomDays').value||'0');
    fd.append('exercises',JSON.stringify(collectExercises()));
    fetch('api.php',{method:'POST',body:fd})
        .then(function(r){ return r.json(); })
        .then(function(res){
            if(res.success){
                showMsg('Program başarıyla kaydedildi.',true);
                document.getElementById('programForm').reset();
                document.getElementById('exerciseList').innerHTML='';
                addExercise();
                loadPrograms();
            }else{
                showMsg(res.error||'Kaydetme hatası.',false);
            }
        })
        .catch(function(){ showMsg('Sunucu hatası.',false); });
});
addExercise();
loadPrograms();

function loadTrainers(){
    fetch('api.php?action=admin_list_trainers')
        .then(function(r){ return r.json(); })
        .then(function(res){
            var rows=document.getElementById('trainerRows');
            var list=res.trainers||[];
            if(!res.success||list.length===0){ rows.innerHTML='<tr><td colspan="4">Antrenör yok.</td></tr>'; return; }
            rows.innerHTML=list.map(function(t){
                return '<tr><td>'+t.full_name+'</td><td>'+t.username+'</td><td>'+t.email+'</td><td>'+t.student_count+'</td></tr>';
            }).join('');
        })
        .catch(function(){});
}
loadTrainers();

var selectedStudentId = null;
function loadStudents(){
    fetch('api.php?action=admin_list_my_users')
        .then(function(r){ return r.json(); })
        .then(function(res){
            var rows=document.getElementById('studentRows');
            var list=res.users||[];
            if(!res.success||list.length===0){ rows.innerHTML='<tr><td colspan="6">Henüz öğrenciniz yok.</td></tr>'; return; }
            rows.innerHTML=list.map(function(u){
                return '<tr><td>'+(u.full_name||'-')+'</td><td>'+u.username+'</td><td>'+(u.last_weight?u.last_weight+' kg':'-')+'</td><td>'+(u.last_log_date||'-')+'</td><td>'+u.log_count+'</td><td><button class="btn primary" onclick="openStudent('+u.id+',\''+((u.full_name||u.username).replace(/'/g,"\\'"))+'\')">Detay</button></td></tr>';
            }).join('');
        })
        .catch(function(){});
}
loadStudents();

function calcBMI(w,h){ if(!w||!h)return null; var hm=h/100; return (w/(hm*hm)).toFixed(1); }
function calcBF(g,waist,neck,hip,h){
    if(!waist||!neck||!h||waist<=neck)return null;
    if(g==='Kadın'){ if(!hip)return null; var bf=163.205*Math.log10(waist+hip-neck)-97.684*Math.log10(h)-78.387; return bf>0&&bf<60?bf.toFixed(1):null; }
    var bf=86.010*Math.log10(waist-neck)-70.041*Math.log10(h)+36.76; return bf>0&&bf<50?bf.toFixed(1):null;
}

function openStudent(uid, name){
    selectedStudentId = uid;
    document.getElementById('sdTitle').textContent = name + ' - Günlük Verileri';
    var now=new Date();
    document.getElementById('sdFrom').value = now.getFullYear()+'-'+String(now.getMonth()+1).padStart(2,'0')+'-01';
    document.getElementById('sdTo').value = now.toISOString().split('T')[0];
    document.getElementById('studentDetail').style.display = 'block';
    loadStudentLogs();
    document.getElementById('studentDetail').scrollIntoView({behavior:'smooth'});
}
function loadStudentLogs(){
    if(!selectedStudentId)return;
    var from=document.getElementById('sdFrom').value;
    var to=document.getElementById('sdTo').value;
    fetch('api.php?action=admin_get_user_logs&user_id='+selectedStudentId+'&from='+from+'&to='+to)
        .then(function(r){ return r.json(); })
        .then(function(res){
            var rows=document.getElementById('sdRows');
            var logs=res.logs||[];
            var gender=(res.user&&res.user.gender)||'Erkek';
            if(logs.length===0){ rows.innerHTML='<tr><td colspan="8">Bu dönemde veri yok.</td></tr>'; return; }
            rows.innerHTML=logs.map(function(l){
                var bmi=calcBMI(parseFloat(l.weight),parseFloat(l.height));
                var bf=calcBF(gender,parseFloat(l.waist),parseFloat(l.neck),parseFloat(l.hip),parseFloat(l.height));
                var wk=parseInt(l.workout_count,10)||0;
                return '<tr><td>'+l.log_date+'</td><td>'+(l.weight||'-')+' kg</td><td>'+(l.height||'-')+' cm</td><td>'+(l.calories_in||'-')+'</td><td>'+(l.waist||'-')+'</td><td>'+(bmi||'-')+'</td><td>'+(bf?bf+'%':'-')+'</td><td>'+(wk>0?wk+' antrenman':'Yok')+'</td></tr>';
            }).join('');
        })
        .catch(function(){});
}
</script>
</body>
</html>
