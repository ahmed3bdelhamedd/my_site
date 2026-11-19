<?php
require __DIR__ . '/config.php';

function db() {
    static $p;
    if (!$p) $p = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    return $p;
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function settings() {
    static $s;
    if ($s === null) { $s = []; try { foreach (db()->query('SELECT k,v FROM settings') as $r) $s[$r['k']] = $r['v']; } catch (Exception $x) {} }
    return $s;
}
function items($sec, $all = false) {
    $q = db()->prepare('SELECT * FROM items WHERE section=?' . ($all ? '' : ' AND visible=1') . ' ORDER BY sort,id');
    $q->execute([$sec]);
    return $q->fetchAll();
}
function tags($s) { return array_filter(array_map('trim', explode(',', (string)$s))); }
function upload($f) {
    if (empty($f['tmp_name']) || $f['error']) return null;
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4'])) return null;
    if ($ext !== 'mp4' && !@getimagesize($f['tmp_name'])) return null;
    $n = bin2hex(random_bytes(8)) . '.' . $ext;
    return move_uploaded_file($f['tmp_name'], __DIR__ . '/uploads/' . $n) ? 'uploads/' . $n : null;
}

// الأقسام: تقدر تضيف قسم جديد أو حقل جديد من هنا. [الاسم، [الحقل => [العنوان، النوع]]]
const SECTIONS = [
    'skills' => ['Skills', ['title' => ['Group name', 'text'], 'tags' => ['Skills (comma separated)', 'text']]],
    'projects' => ['Projects', ['title' => ['Title', 'text'], 'body' => ['Description', 'area'], 'tags' => ['Technologies (comma separated)', 'text'], 'image' => ['Screenshot', 'file'], 'link' => ['Link (optional)', 'text']]],
    'courses' => ['Courses', ['title' => ['Title', 'text'], 'subtitle' => ['Level', 'text'], 'body' => ['Description', 'area']]],
    'certificates' => ['Certificates', ['title' => ['Title', 'text'], 'subtitle' => ['Issuer / note', 'text'], 'image' => ['Certificate image', 'file']]],
    'experience' => ['Experience', ['title' => ['Role', 'text'], 'subtitle' => ['Company', 'text'], 'meta' => ['Dates', 'text'], 'body' => ['Description', 'area'], 'tags' => ['Tags (comma separated)', 'text']]],
];
const SETTINGS = [
    'name' => ['Name', 'text'], 'tagline' => ['Tagline', 'text'], 'badge' => ['Small badge text', 'text'],
    'intro' => ['Hero intro', 'area'], 'photo' => ['Profile photo', 'file'],
    'about_title' => ['About title', 'text'], 'about_text' => ['About text (blank line = new paragraph)', 'area'],
    'about_points' => ['About bullet points (one per line)', 'area'],
    'video_drive_url' => ['Intro video: Google Drive link (anyone with the link)', 'text'],
    'video_file' => ['Intro video: or upload an MP4 (overrides Drive)', 'file'], 'video_poster' => ['Video cover image', 'file'],
    'cv_url' => ['CV link', 'text'], 'email' => ['Email', 'text'], 'phone' => ['Phone', 'text'], 'whatsapp' => ['WhatsApp number (e.g. 201116512028)', 'text'],
    'location' => ['Location', 'text'], 'linkedin' => ['LinkedIn URL', 'text'], 'behance' => ['Behance URL', 'text'],
];
