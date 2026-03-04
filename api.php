<?php
require_once 'session-check.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Lutfen giris yapin.']);
    exit;
}

$uid = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

function ok($data = []) { echo json_encode(array_merge(['success' => true], $data)); exit; }
function fail($msg, $code = 400) { http_response_code($code); echo json_encode(['success' => false, 'error' => $msg]); exit; }

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

    case 'admin_list_programs':
        if (!isAdmin()) fail('Yetkisiz.', 403);
        $q = $conn->query("SELECT p.id,p.name,p.description,p.duration_type,p.custom_days,p.created_at,COUNT(e.id) exercise_count
                           FROM program_templates p LEFT JOIN program_exercises e ON e.program_id=p.id
                           GROUP BY p.id ORDER BY p.id DESC");
        ok(['programs' => $q ? $q->fetch_all(MYSQLI_ASSOC) : []]);
        break;

    case 'list_program_templates':
        $q = $conn->query("SELECT id,name,description,duration_type,custom_days,created_at FROM program_templates ORDER BY id DESC");
        ok(['programs' => $q ? $q->fetch_all(MYSQLI_ASSOC) : []]);
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
        $s = $conn->prepare("SELECT username,email,full_name,phone,age,gender,address FROM users WHERE id=?");
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

    default:
        fail('Bilinmeyen islem.', 404);
}
?>
