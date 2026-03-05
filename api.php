<?php
require_once 'session-check.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

function ok($data = []) { echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE); exit; }
function fail($msg, $code = 400) { http_response_code($code); echo json_encode(['success' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE); exit; }

if ($action === 'public_programs') {
    $q = $conn->query("SELECT p.id, p.name, p.description, p.duration_type, p.custom_days,
                        (SELECT COUNT(*) FROM program_exercises e WHERE e.program_id=p.id) as exercise_count,
                        u.full_name as trainer_name, u.username as trainer_username
                        FROM program_templates p
                        LEFT JOIN users u ON u.id = p.created_by
                        ORDER BY p.id DESC");
    ok(['programs' => $q ? $q->fetch_all(MYSQLI_ASSOC) : []]);
}

if ($action === 'list_trainers') {
    $q = $conn->query("SELECT id, full_name, username FROM users WHERE is_admin=1 AND is_active=1 ORDER BY full_name, username");
    ok(['trainers' => $q ? $q->fetch_all(MYSQLI_ASSOC) : []]);
}

if ($action === 'send_contact') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name === '' || $email === '' || $message === '') fail('Ad, email ve mesaj zorunludur.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Geçerli bir email girin.');
    $contactEmail = getSetting('contact_email', 'info@fitness101.com');
    $firstEmail = trim(strtok($contactEmail, "\n"));
    $smtpFrom = getSetting('smtp_from_email', 'noreply@fitness101.com');
    $smtpName = getSetting('smtp_from_name', 'FİTNESS101');
    $subject = 'FİTNESS101 İletişim Formu - ' . $name;
    $body = "İsim: $name\nEmail: $email\nTelefon: $phone\n\nMesaj:\n$message";
    $headers = "From: $smtpName <$smtpFrom>\r\nReply-To: $name <$email>\r\nContent-Type: text/plain; charset=UTF-8";
    if (@mail($firstEmail, $subject, $body, $headers)) {
        ok(['message' => 'Mesajınız gönderildi.']);
    } else {
        fail('Mesaj gönderilemedi. Lütfen daha sonra tekrar deneyin.');
    }
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Lutfen giris yapin.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$uid = (int)$_SESSION['user_id'];

function duration_days($type, $custom) {
    if ($type === 'haftalik') return 7;
    if ($type === 'aylik') return 30;
    if ($type === 'uc_aylik') return 90;
    if ($type === 'ozel') return max(1, (int)$custom);
    return 7;
}

function parse_days($csv) {
    $parts = array_filter(array_map('trim', explode(',', (string)$csv)));
    $days = [];
    foreach ($parts as $p) {
        $d = (int)$p;
        if ($d >= 1 && $d <= 7) $days[] = $d;
    }
    $days = array_values(array_unique($days));
    sort($days);
    return $days;
}

function schedule_dates($startDate, $durationDays, $days) {
    $dates = [];
    $cur = new DateTime($startDate);
    $end = (clone $cur)->modify('+' . ($durationDays - 1) . ' day');
    while ($cur <= $end) {
        if (in_array((int)$cur->format('N'), $days, true)) $dates[] = $cur->format('Y-m-d');
        $cur->modify('+1 day');
    }
    if (!$dates) $dates[] = (new DateTime($startDate))->format('Y-m-d');
    return $dates;
}

switch ($action) {
    case 'admin_create_program':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $durationType = trim($_POST['duration_type'] ?? '');
        $customDays = (int)($_POST['custom_days'] ?? 0);
        $exercises = json_decode($_POST['exercises'] ?? '[]', true);

        if ($name === '') fail('Program adi zorunlu.');
        if (!in_array($durationType, ['haftalik', 'aylik', 'uc_aylik', 'ozel'], true)) fail('Sure tipi gecersiz.');
        if ($durationType === 'ozel' && $customDays <= 0) fail('Ozel surede gun sayisi girin.');
        if (!is_array($exercises) || count($exercises) === 0) fail('En az bir hareket ekleyin.');

        global $conn;
        $conn->begin_transaction();
        try {
            $custom = $durationType === 'ozel' ? $customDays : null;
            $p = $conn->prepare("INSERT INTO program_templates (name, description, duration_type, custom_days, created_by) VALUES (?, ?, ?, ?, ?)");
            $p->bind_param('sssii', $name, $description, $durationType, $custom, $uid);
            $p->execute();
            $programId = (int)$conn->insert_id;
            $p->close();

            $e = $conn->prepare("INSERT INTO program_exercises (program_id, title, description, youtube_url, sort_order) VALUES (?, ?, ?, ?, ?)");
            $order = 1;
            foreach ($exercises as $row) {
                $title = trim((string)($row['title'] ?? ''));
                $desc = trim((string)($row['description'] ?? ''));
                $url = trim((string)($row['youtube_url'] ?? ''));
                if ($title === '') continue;
                $e->bind_param('isssi', $programId, $title, $desc, $url, $order);
                $e->execute();
                $order++;
            }
            $e->close();
            $conn->commit();
            ok(['program_id' => $programId]);
        } catch (Throwable $err) {
            $conn->rollback();
            fail('Program kaydedilemedi.');
        }
        break;

    case 'admin_create_trainer':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        $tusername = trim($_POST['username'] ?? '');
        $temail = trim($_POST['email'] ?? '');
        $tfullname = trim($_POST['full_name'] ?? '');
        $tpassword = $_POST['password'] ?? '';
        if ($tusername === '' || $temail === '' || $tpassword === '') fail('Tüm zorunlu alanları doldurun.');
        if (strlen($tpassword) < 8) fail('Şifre en az 8 karakter olmalıdır.');
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $tusername)) fail('Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir (3-50 karakter).');
        if (!filter_var($temail, FILTER_VALIDATE_EMAIL)) fail('Geçerli bir email girin.');

        $dup = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
        $dup->bind_param('ss', $tusername, $temail);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) { $dup->close(); fail('Bu kullanıcı adı veya email zaten kullanılıyor.'); }
        $dup->close();

        $hashed = hash('sha256', $tpassword);
        $ins = $conn->prepare("INSERT INTO users (username, email, password, full_name, is_admin, is_active) VALUES (?, ?, ?, ?, 1, 1)");
        $ins->bind_param('ssss', $tusername, $temail, $hashed, $tfullname);
        if ($ins->execute()) {
            $newId = (int)$conn->insert_id;
            $ins->close();
            ok(['trainer_id' => $newId]);
        } else {
            $ins->close();
            fail('Antrenör oluşturulamadı.');
        }
        break;

    case 'admin_list_trainers':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        $q = $conn->query("SELECT u.id, u.full_name, u.username, u.email,
                           (SELECT COUNT(*) FROM users s WHERE s.trainer_id=u.id) as student_count
                           FROM users u WHERE u.is_admin=1 ORDER BY u.full_name, u.username");
        ok(['trainers' => $q ? $q->fetch_all(MYSQLI_ASSOC) : []]);
        break;

    case 'admin_list_programs':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        $q = $conn->query("SELECT p.id,p.name,p.description,p.duration_type,p.custom_days,p.created_at,COUNT(e.id) exercise_count
                           FROM program_templates p LEFT JOIN program_exercises e ON e.program_id=p.id
                           GROUP BY p.id ORDER BY p.id DESC");
        ok(['programs' => $q ? $q->fetch_all(MYSQLI_ASSOC) : []]);
        break;

    case 'list_program_templates':
        $trainerFilter = $conn->prepare("SELECT trainer_id FROM users WHERE id=? LIMIT 1");
        $trainerFilter->bind_param('i', $uid);
        $trainerFilter->execute();
        $trainerRow = $trainerFilter->get_result()->fetch_assoc();
        $trainerFilter->close();
        $myTrainerId = $trainerRow ? (int)$trainerRow['trainer_id'] : 0;

        if ($myTrainerId > 0) {
            $q = $conn->prepare("SELECT p.id,p.name,p.description,p.duration_type,p.custom_days,p.created_at,
                                 u.full_name as trainer_name
                                 FROM program_templates p
                                 LEFT JOIN users u ON u.id=p.created_by
                                 WHERE p.created_by=?
                                 ORDER BY p.id DESC");
            $q->bind_param('i', $myTrainerId);
            $q->execute();
            $programs = $q->get_result()->fetch_all(MYSQLI_ASSOC);
            $q->close();
        } else {
            $q = $conn->query("SELECT p.id,p.name,p.description,p.duration_type,p.custom_days,p.created_at,
                               u.full_name as trainer_name
                               FROM program_templates p
                               LEFT JOIN users u ON u.id=p.created_by
                               ORDER BY p.id DESC");
            $programs = $q ? $q->fetch_all(MYSQLI_ASSOC) : [];
        }
        ok(['programs' => $programs]);
        break;

    case 'get_program_exercises':
        $pid = (int)($_GET['program_id'] ?? 0);
        if ($pid <= 0) fail('Program gecersiz.');
        $s = $conn->prepare("SELECT id,title,description,youtube_url,sort_order FROM program_exercises WHERE program_id=? ORDER BY sort_order,id");
        $s->bind_param('i', $pid);
        $s->execute();
        $rows = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $s->close();
        ok(['exercises' => $rows]);
        break;

    case 'assign_program':
        $pid = (int)($_POST['program_id'] ?? 0);
        $start = trim($_POST['start_date'] ?? date('Y-m-d'));
        $days = parse_days($_POST['selected_days'] ?? '');
        if ($pid <= 0) fail('Program secin.');
        if (!$days) fail('En az bir gun secin.');

        $s = $conn->prepare("SELECT duration_type,custom_days FROM program_templates WHERE id=? LIMIT 1");
        $s->bind_param('i', $pid);
        $s->execute();
        $p = $s->get_result()->fetch_assoc();
        $s->close();
        if (!$p) fail('Program bulunamadi.');

        $dDays = duration_days($p['duration_type'], $p['custom_days']);
        $end = (new DateTime($start))->modify('+' . ($dDays - 1) . ' day')->format('Y-m-d');
        $dates = schedule_dates($start, $dDays, $days);

        $conn->begin_transaction();
        try {
            $a = $conn->prepare("INSERT INTO user_program_assignments (user_id,program_id,start_date,end_date,selected_days) VALUES (?,?,?,?,?)");
            $csv = implode(',', $days);
            $a->bind_param('iisss', $uid, $pid, $start, $end, $csv);
            $a->execute();
            $aid = (int)$conn->insert_id;
            $a->close();

            $ins = $conn->prepare("INSERT INTO assignment_schedule (assignment_id,scheduled_date) VALUES (?,?)");
            foreach ($dates as $dt) {
                $ins->bind_param('is', $aid, $dt);
                $ins->execute();
            }
            $ins->close();
            $conn->commit();
            ok(['assignment_id' => $aid]);
        } catch (Throwable $e) {
            $conn->rollback();
            fail('Program atanamadi.');
        }
        break;

    case 'my_assignments':
        $s = $conn->prepare("SELECT a.id,a.program_id,a.start_date,a.end_date,a.selected_days,a.status,p.name,p.description,p.duration_type,p.custom_days
                             FROM user_program_assignments a JOIN program_templates p ON p.id=a.program_id
                             WHERE a.user_id=? ORDER BY a.id DESC");
        $s->bind_param('i', $uid);
        $s->execute();
        $rows = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $s->close();
        ok(['assignments' => $rows]);
        break;

    case 'assignment_schedule':
        $aid = (int)($_GET['assignment_id'] ?? 0);
        if ($aid <= 0) fail('Atama gecersiz.');
        $s = $conn->prepare("SELECT a.id,a.program_id,p.name,p.description FROM user_program_assignments a JOIN program_templates p ON p.id=a.program_id WHERE a.id=? AND a.user_id=? LIMIT 1");
        $s->bind_param('ii', $aid, $uid);
        $s->execute();
        $assign = $s->get_result()->fetch_assoc();
        $s->close();
        if (!$assign) fail('Atama bulunamadi.');

        $s = $conn->prepare("SELECT id,scheduled_date,is_completed,completed_at FROM assignment_schedule WHERE assignment_id=? ORDER BY scheduled_date");
        $s->bind_param('i', $aid);
        $s->execute();
        $schedule = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $s->close();

        $s = $conn->prepare("SELECT id,title,description,youtube_url,sort_order FROM program_exercises WHERE program_id=? ORDER BY sort_order,id");
        $s->bind_param('i', $assign['program_id']);
        $s->execute();
        $ex = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $s->close();
        ok(['assignment' => $assign, 'schedule' => $schedule, 'exercises' => $ex]);
        break;

    case 'toggle_schedule_done':
        $sid = (int)($_POST['schedule_id'] ?? 0);
        if ($sid <= 0) fail('Plan kaydi gecersiz.');
        $s = $conn->prepare("SELECT s.id,s.is_completed,s.assignment_id,p.name
                             FROM assignment_schedule s
                             JOIN user_program_assignments a ON a.id=s.assignment_id
                             JOIN program_templates p ON p.id=a.program_id
                             WHERE s.id=? AND a.user_id=? LIMIT 1");
        $s->bind_param('ii', $sid, $uid);
        $s->execute();
        $row = $s->get_result()->fetch_assoc();
        $s->close();
        if (!$row) fail('Kayit bulunamadi.');

        $new = ((int)$row['is_completed'] === 1) ? 0 : 1;
        $completedAt = $new === 1 ? date('Y-m-d H:i:s') : null;
        $u = $conn->prepare("UPDATE assignment_schedule SET is_completed=?, completed_at=? WHERE id=?");
        $u->bind_param('isi', $new, $completedAt, $sid);
        $u->execute();
        $u->close();

        if ($new === 1) {
            $note = 'Gunluk plan tamamlandi.';
            $w = $conn->prepare("INSERT INTO workout_history (user_id, program_name, duration_min, calories, notes) VALUES (?, ?, 60, 350, ?)");
            $w->bind_param('iss', $uid, $row['name'], $note);
            $w->execute();
            $w->close();
        }
        ok(['is_completed' => $new === 1]);
        break;

    case 'get_workouts':
        $s = $conn->prepare("SELECT id,program_name,duration_min,calories,notes,workout_date FROM workout_history WHERE user_id=? ORDER BY workout_date DESC LIMIT 100");
        $s->bind_param('i', $uid);
        $s->execute();
        $rows = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $s->close();
        ok(['workouts' => $rows]);
        break;

    case 'get_stats':
        $total = 0; $month = 0; $last = '-'; $active = 0;
        $s = $conn->prepare("SELECT COUNT(*) c FROM workout_history WHERE user_id=?");
        $s->bind_param('i', $uid); $s->execute(); $total = (int)$s->get_result()->fetch_assoc()['c']; $s->close();
        $s = $conn->prepare("SELECT COUNT(*) c FROM workout_history WHERE user_id=? AND MONTH(workout_date)=MONTH(NOW()) AND YEAR(workout_date)=YEAR(NOW())");
        $s->bind_param('i', $uid); $s->execute(); $month = (int)$s->get_result()->fetch_assoc()['c']; $s->close();
        $s = $conn->prepare("SELECT workout_date FROM workout_history WHERE user_id=? ORDER BY workout_date DESC LIMIT 1");
        $s->bind_param('i', $uid); $s->execute(); $r = $s->get_result()->fetch_assoc(); $s->close();
        if ($r) $last = $r['workout_date'];
        $s = $conn->prepare("SELECT COUNT(*) c FROM user_program_assignments WHERE user_id=? AND status='Aktif'");
        $s->bind_param('i', $uid); $s->execute(); $active = (int)$s->get_result()->fetch_assoc()['c']; $s->close();
        ok(['total_workouts' => $total, 'month_workouts' => $month, 'last_workout' => $last, 'active_programs' => $active]);
        break;

    case 'get_profile':
        $s = $conn->prepare("SELECT u.username, u.email, u.full_name, u.phone, u.age, u.gender, u.address, u.trainer_id,
                             t.full_name as trainer_name, t.username as trainer_username
                             FROM users u
                             LEFT JOIN users t ON t.id = u.trainer_id
                             WHERE u.id=?");
        $s->bind_param('i', $uid);
        $s->execute();
        $p = $s->get_result()->fetch_assoc();
        $s->close();
        ok(['profile' => $p ?: []]);
        break;

    case 'update_profile':
        $fn = trim($_POST['full_name'] ?? '');
        $ph = trim($_POST['phone'] ?? '');
        $ag = ($_POST['age'] ?? '') === '' ? null : (int)$_POST['age'];
        $gn = trim($_POST['gender'] ?? '');
        $ad = trim($_POST['address'] ?? '');
        $gn = $gn === '' ? null : $gn;
        $s = $conn->prepare("UPDATE users SET full_name=?, phone=?, age=?, gender=?, address=? WHERE id=?");
        $s->bind_param('ssissi', $fn, $ph, $ag, $gn, $ad, $uid);
        $okv = $s->execute();
        $s->close();
        if ($okv) $_SESSION['full_name'] = $fn;
        ok(['updated' => (bool)$okv]);
        break;

    case 'change_trainer':
        $newTrainerId = (int)($_POST['trainer_id'] ?? 0);
        if ($newTrainerId <= 0) fail('Geçersiz antrenör.');
        $tc = $conn->prepare("SELECT id FROM users WHERE id=? AND is_admin=1 AND is_active=1");
        $tc->bind_param('i', $newTrainerId);
        $tc->execute();
        if ($tc->get_result()->num_rows === 0) { $tc->close(); fail('Seçilen antrenör bulunamadı.'); }
        $tc->close();
        $up = $conn->prepare("UPDATE users SET trainer_id=? WHERE id=?");
        $up->bind_param('ii', $newTrainerId, $uid);
        $okv = $up->execute();
        $up->close();
        ok(['updated' => (bool)$okv]);
        break;

    case 'save_settings':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        if (empty($_SESSION['manager_auth'])) fail('Yönetici doğrulaması gerekli.', 403);
        $raw = $_POST['settings'] ?? '{}';
        $data = json_decode($raw, true);
        if (!is_array($data)) fail('Geçersiz veri.');
        $allowed = ['hero_title','hero_subtitle',
            'feature_1_icon','feature_1_title','feature_1_desc',
            'feature_2_icon','feature_2_title','feature_2_desc',
            'feature_3_icon','feature_3_title','feature_3_desc',
            'feature_4_icon','feature_4_title','feature_4_desc',
            'stat_1_value','stat_1_label','stat_2_value','stat_2_label','stat_3_value','stat_3_label',
            'cta_title','cta_subtitle',
            'contact_address','contact_phone','contact_email','contact_hours',
            'smtp_host','smtp_port','smtp_user','smtp_pass','smtp_from_email','smtp_from_name',
            'manager_password'];
        $up = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        foreach ($data as $k => $v) {
            if (!in_array($k, $allowed, true)) continue;
            $up->bind_param('ss', $k, $v);
            $up->execute();
        }
        $up->close();
        ok([]);
        break;

    case 'test_email':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        if (empty($_SESSION['manager_auth'])) fail('Yönetici doğrulaması gerekli.', 403);
        $smtpHost = getSetting('smtp_host');
        $smtpFrom = getSetting('smtp_from_email', 'test@fitness101.com');
        $smtpName = getSetting('smtp_from_name', 'FİTNESS101');
        $smtpUser = getSetting('smtp_user');
        $toEmail = $smtpUser ?: $smtpFrom;
        if (!$toEmail) fail('Gönderici email ayarlanmamış.');
        $subject = 'FİTNESS101 Test Email';
        $body = 'Bu bir test emaildir. Email sisteminiz çalışıyor!';
        $headers = "From: $smtpName <$smtpFrom>\r\nContent-Type: text/plain; charset=UTF-8";
        if (@mail($toEmail, $subject, $body, $headers)) {
            ok(['message' => 'Test email gönderildi: ' . $toEmail]);
        } else {
            fail('Email gönderilemedi. Sunucu mail() fonksiyonunu desteklemiyor olabilir.');
        }
        break;

    case 'save_daily_log':
        $logDate = trim($_POST['log_date'] ?? date('Y-m-d'));
        $weight = ($_POST['weight'] ?? '') !== '' ? (float)$_POST['weight'] : null;
        $height = ($_POST['height'] ?? '') !== '' ? (float)$_POST['height'] : null;
        $caloriesIn = (int)($_POST['calories_in'] ?? 0);
        $waist = ($_POST['waist'] ?? '') !== '' ? (float)$_POST['waist'] : null;
        $neck = ($_POST['neck'] ?? '') !== '' ? (float)$_POST['neck'] : null;
        $hip = ($_POST['hip'] ?? '') !== '' ? (float)$_POST['hip'] : null;
        $chest = ($_POST['chest'] ?? '') !== '' ? (float)$_POST['chest'] : null;
        $arm = ($_POST['arm'] ?? '') !== '' ? (float)$_POST['arm'] : null;
        $shoulder = ($_POST['shoulder'] ?? '') !== '' ? (float)$_POST['shoulder'] : null;
        $logNotes = trim($_POST['notes'] ?? '');

        if (!$weight || $weight <= 0) fail('Kilo zorunludur.');
        if (!$height || $height <= 0) {
            $prev = $conn->prepare("SELECT height FROM daily_logs WHERE user_id=? AND height IS NOT NULL ORDER BY log_date DESC LIMIT 1");
            $prev->bind_param('i', $uid);
            $prev->execute();
            $pr = $prev->get_result()->fetch_assoc();
            $prev->close();
            if ($pr) $height = (float)$pr['height'];
            else fail('Boy bilgisi zorunludur (ilk girişte).');
        }

        $sql = "INSERT INTO daily_logs (user_id,log_date,weight,height,calories_in,waist,neck,hip,chest,arm,shoulder,notes)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE weight=VALUES(weight),height=VALUES(height),calories_in=VALUES(calories_in),
                waist=VALUES(waist),neck=VALUES(neck),hip=VALUES(hip),chest=VALUES(chest),
                arm=VALUES(arm),shoulder=VALUES(shoulder),notes=VALUES(notes)";
        $s = $conn->prepare($sql);
        if (!$s) fail('Veritabanı hatası: ' . $conn->error);
        $s->bind_param('isddidddddds', $uid, $logDate, $weight, $height, $caloriesIn, $waist, $neck, $hip, $chest, $arm, $shoulder, $logNotes);
        if (!$s->execute()) {
            $err = $s->error;
            $s->close();
            fail('Kayıt hatası: ' . $err);
        }
        $s->close();

        ok([]);
        break;

    case 'get_daily_logs':
        $from = trim($_GET['from'] ?? date('Y-m-01'));
        $to = trim($_GET['to'] ?? date('Y-m-d'));
        $targetUid = $uid;
        if (isAdmin() && !empty($_GET['user_id'])) $targetUid = (int)$_GET['user_id'];

        $s = $conn->prepare("SELECT d.*, 
                             (SELECT COUNT(*) FROM workout_history w WHERE w.user_id=d.user_id AND DATE(w.workout_date)=d.log_date) as workout_count,
                             (SELECT SUM(w.calories) FROM workout_history w WHERE w.user_id=d.user_id AND DATE(w.workout_date)=d.log_date) as calories_burned
                             FROM daily_logs d
                             WHERE d.user_id=? AND d.log_date BETWEEN ? AND ?
                             ORDER BY d.log_date DESC");
        $s->bind_param('iss', $targetUid, $from, $to);
        $s->execute();
        $logs = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $s->close();
        ok(['logs' => $logs]);
        break;

    case 'get_daily_stats':
        $period = trim($_GET['period'] ?? 'month');
        $targetUid = $uid;
        if (isAdmin() && !empty($_GET['user_id'])) $targetUid = (int)$_GET['user_id'];

        if ($period === 'week') {
            $from = date('Y-m-d', strtotime('-7 days'));
        } elseif ($period === '3month') {
            $from = date('Y-m-d', strtotime('-3 months'));
        } elseif ($period === 'year') {
            $from = date('Y-m-d', strtotime('-1 year'));
        } else {
            $from = date('Y-m-01');
        }
        $to = date('Y-m-d');

        $s = $conn->prepare("SELECT d.log_date, d.weight, d.height, d.calories_in, d.waist, d.neck, d.hip, d.chest, d.arm, d.shoulder,
                             (SELECT SUM(w.calories) FROM workout_history w WHERE w.user_id=d.user_id AND DATE(w.workout_date)=d.log_date) as calories_burned,
                             (SELECT COUNT(*) FROM workout_history w WHERE w.user_id=d.user_id AND DATE(w.workout_date)=d.log_date) as workout_count
                             FROM daily_logs d
                             WHERE d.user_id=? AND d.log_date BETWEEN ? AND ?
                             ORDER BY d.log_date ASC");
        $s->bind_param('iss', $targetUid, $from, $to);
        $s->execute();
        $logs = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $s->close();

        $genderQ = $conn->prepare("SELECT gender FROM users WHERE id=?");
        $genderQ->bind_param('i', $targetUid);
        $genderQ->execute();
        $gRow = $genderQ->get_result()->fetch_assoc();
        $genderQ->close();
        $gender = $gRow ? $gRow['gender'] : 'Erkek';

        ok(['logs' => $logs, 'gender' => $gender, 'from' => $from, 'to' => $to]);
        break;

    case 'admin_get_user_logs':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        $targetUid = (int)($_GET['user_id'] ?? 0);
        if ($targetUid <= 0) fail('Kullanıcı seçin.');
        $from = trim($_GET['from'] ?? date('Y-m-01'));
        $to = trim($_GET['to'] ?? date('Y-m-d'));

        $uInfo = $conn->prepare("SELECT id,username,full_name,gender,age FROM users WHERE id=?");
        $uInfo->bind_param('i', $targetUid);
        $uInfo->execute();
        $userInfo = $uInfo->get_result()->fetch_assoc();
        $uInfo->close();
        if (!$userInfo) fail('Kullanıcı bulunamadı.');

        $s = $conn->prepare("SELECT d.*,
                             (SELECT COUNT(*) FROM workout_history w WHERE w.user_id=d.user_id AND DATE(w.workout_date)=d.log_date) as workout_count,
                             (SELECT SUM(w.calories) FROM workout_history w WHERE w.user_id=d.user_id AND DATE(w.workout_date)=d.log_date) as calories_burned
                             FROM daily_logs d
                             WHERE d.user_id=? AND d.log_date BETWEEN ? AND ?
                             ORDER BY d.log_date DESC");
        $s->bind_param('iss', $targetUid, $from, $to);
        $s->execute();
        $logs = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $s->close();
        ok(['user' => $userInfo, 'logs' => $logs]);
        break;

    case 'delete_daily_log':
        $logId = (int)($_POST['log_id'] ?? 0);
        if ($logId <= 0) fail('Geçersiz kayıt.');
        $d = $conn->prepare("DELETE FROM daily_logs WHERE id=? AND user_id=?");
        $d->bind_param('ii', $logId, $uid);
        $d->execute();
        $affected = $d->affected_rows;
        $d->close();
        if ($affected > 0) ok([]);
        else fail('Kayıt bulunamadı veya yetkiniz yok.');
        break;

    case 'manager_list_all_users':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        if (empty($_SESSION['manager_auth'])) fail('Yönetici doğrulaması gerekli.', 403);
        $q = $conn->query("SELECT u.id, u.username, u.email, u.full_name, u.phone, u.age, u.gender, u.address,
                            u.is_admin, u.is_active, u.trainer_id, u.created_at, u.last_login,
                            t.full_name as trainer_name, t.username as trainer_username
                            FROM users u
                            LEFT JOIN users t ON t.id = u.trainer_id
                            ORDER BY u.id ASC");
        ok(['users' => $q ? $q->fetch_all(MYSQLI_ASSOC) : []]);
        break;

    case 'manager_get_user':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        if (empty($_SESSION['manager_auth'])) fail('Yönetici doğrulaması gerekli.', 403);
        $targetId = (int)($_GET['user_id'] ?? 0);
        if ($targetId <= 0) fail('Geçersiz kullanıcı.');
        $s = $conn->prepare("SELECT u.id, u.username, u.email, u.full_name, u.phone, u.age, u.gender, u.address,
                              u.is_admin, u.is_active, u.trainer_id, u.created_at, u.last_login, u.password,
                              t.full_name as trainer_name
                              FROM users u LEFT JOIN users t ON t.id = u.trainer_id
                              WHERE u.id=?");
        $s->bind_param('i', $targetId);
        $s->execute();
        $row = $s->get_result()->fetch_assoc();
        $s->close();
        if (!$row) fail('Kullanıcı bulunamadı.');
        ok(['user' => $row]);
        break;

    case 'manager_update_user':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        if (empty($_SESSION['manager_auth'])) fail('Yönetici doğrulaması gerekli.', 403);
        $targetId = (int)($_POST['user_id'] ?? 0);
        if ($targetId <= 0) fail('Geçersiz kullanıcı.');

        $fields = [];
        $types = '';
        $vals = [];

        $uname = trim($_POST['username'] ?? '');
        if ($uname !== '') {
            $dup = $conn->prepare("SELECT id FROM users WHERE username=? AND id!=? LIMIT 1");
            $dup->bind_param('si', $uname, $targetId);
            $dup->execute();
            if ($dup->get_result()->num_rows > 0) { $dup->close(); fail('Bu kullanıcı adı zaten kullanılıyor.'); }
            $dup->close();
            $fields[] = 'username=?'; $types .= 's'; $vals[] = $uname;
        }
        $uemail = trim($_POST['email'] ?? '');
        if ($uemail !== '') {
            if (!filter_var($uemail, FILTER_VALIDATE_EMAIL)) fail('Geçerli bir email girin.');
            $dup = $conn->prepare("SELECT id FROM users WHERE email=? AND id!=? LIMIT 1");
            $dup->bind_param('si', $uemail, $targetId);
            $dup->execute();
            if ($dup->get_result()->num_rows > 0) { $dup->close(); fail('Bu email zaten kullanılıyor.'); }
            $dup->close();
            $fields[] = 'email=?'; $types .= 's'; $vals[] = $uemail;
        }
        if (isset($_POST['full_name'])) { $fields[] = 'full_name=?'; $types .= 's'; $vals[] = trim($_POST['full_name']); }
        if (isset($_POST['phone'])) { $fields[] = 'phone=?'; $types .= 's'; $vals[] = trim($_POST['phone']); }
        if (isset($_POST['age'])) {
            $ageVal = $_POST['age'] === '' ? null : (int)$_POST['age'];
            $fields[] = 'age=?'; $types .= 'i'; $vals[] = $ageVal;
        }
        if (isset($_POST['gender'])) {
            $gVal = trim($_POST['gender']);
            $fields[] = 'gender=?'; $types .= 's'; $vals[] = ($gVal === '' ? null : $gVal);
        }
        if (isset($_POST['address'])) { $fields[] = 'address=?'; $types .= 's'; $vals[] = trim($_POST['address']); }
        if (isset($_POST['is_admin'])) { $fields[] = 'is_admin=?'; $types .= 'i'; $vals[] = (int)$_POST['is_admin']; }
        if (isset($_POST['is_active'])) { $fields[] = 'is_active=?'; $types .= 'i'; $vals[] = (int)$_POST['is_active']; }
        if (isset($_POST['trainer_id'])) {
            $tid = $_POST['trainer_id'] === '' ? null : (int)$_POST['trainer_id'];
            $fields[] = 'trainer_id=?'; $types .= 'i'; $vals[] = $tid;
        }

        $newPass = $_POST['new_password'] ?? '';
        if ($newPass !== '') {
            if (strlen($newPass) < 6) fail('Şifre en az 6 karakter olmalı.');
            $fields[] = 'password=?'; $types .= 's'; $vals[] = hash('sha256', $newPass);
        }

        if (empty($fields)) fail('Güncellenecek alan yok.');

        $types .= 'i';
        $vals[] = $targetId;
        $sql = "UPDATE users SET " . implode(',', $fields) . " WHERE id=?";
        $s = $conn->prepare($sql);
        $s->bind_param($types, ...$vals);
        $okv = $s->execute();
        $s->close();
        ok(['updated' => (bool)$okv]);
        break;

    case 'manager_delete_user':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        if (empty($_SESSION['manager_auth'])) fail('Yönetici doğrulaması gerekli.', 403);
        $targetId = (int)($_POST['user_id'] ?? 0);
        if ($targetId <= 0) fail('Geçersiz kullanıcı.');
        if ($targetId === $uid) fail('Kendinizi silemezsiniz.');
        $d = $conn->prepare("DELETE FROM users WHERE id=?");
        $d->bind_param('i', $targetId);
        $d->execute();
        $affected = $d->affected_rows;
        $d->close();
        if ($affected > 0) ok([]);
        else fail('Kullanıcı bulunamadı.');
        break;

    case 'manager_create_user':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        if (empty($_SESSION['manager_auth'])) fail('Yönetici doğrulaması gerekli.', 403);
        $tusername = trim($_POST['username'] ?? '');
        $temail = trim($_POST['email'] ?? '');
        $tfullname = trim($_POST['full_name'] ?? '');
        $tpassword = $_POST['password'] ?? '';
        $tisAdmin = (int)($_POST['is_admin'] ?? 0);
        if ($tusername === '' || $temail === '' || $tpassword === '') fail('Kullanıcı adı, email ve şifre zorunludur.');
        if (strlen($tpassword) < 6) fail('Şifre en az 6 karakter olmalı.');
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $tusername)) fail('Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir (3-50 karakter).');
        if (!filter_var($temail, FILTER_VALIDATE_EMAIL)) fail('Geçerli bir email girin.');

        $dup = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
        $dup->bind_param('ss', $tusername, $temail);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) { $dup->close(); fail('Bu kullanıcı adı veya email zaten kullanılıyor.'); }
        $dup->close();

        $hashed = hash('sha256', $tpassword);
        $ins = $conn->prepare("INSERT INTO users (username, email, password, full_name, is_admin, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $ins->bind_param('ssssi', $tusername, $temail, $hashed, $tfullname, $tisAdmin);
        if ($ins->execute()) {
            $newId = (int)$conn->insert_id;
            $ins->close();
            ok(['user_id' => $newId]);
        } else {
            $ins->close();
            fail('Kullanıcı oluşturulamadı.');
        }
        break;

    case 'admin_list_my_users':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        $s = $conn->prepare("SELECT u.id, u.username, u.full_name, u.gender, u.age,
                             (SELECT COUNT(*) FROM daily_logs d WHERE d.user_id=u.id) as log_count,
                             (SELECT d.weight FROM daily_logs d WHERE d.user_id=u.id ORDER BY d.log_date DESC LIMIT 1) as last_weight,
                             (SELECT d.log_date FROM daily_logs d WHERE d.user_id=u.id ORDER BY d.log_date DESC LIMIT 1) as last_log_date
                             FROM users u WHERE u.trainer_id=? AND u.is_admin=0 ORDER BY u.full_name, u.username");
        $s->bind_param('i', $uid);
        $s->execute();
        $users = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $s->close();
        ok(['users' => $users]);
        break;

    default:
        fail('Bilinmeyen islem.', 404);
}
?>
