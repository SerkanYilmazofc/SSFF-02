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

        <div class="card">
            <h2>Program Listesi</h2>
            <table>
                <thead><tr><th>Program</th><th>Süre</th><th>Hareket</th><th>Tarih</th></tr></thead>
                <tbody id="programRows"><tr><td colspan="4">Yükleniyor...</td></tr></tbody>
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
</script>
</body>
</html>
