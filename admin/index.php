<?php
require __DIR__ . '/../lib.php';
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
if (isset($_GET['logout'])) { session_destroy(); header('Location: ./'); exit; }

$css = '<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>
body{font:15px/1.5 system-ui,sans-serif;margin:0;background:#f3f4f7;color:#14161a}.w{max-width:900px;margin:auto;padding:20px}
nav{background:#14161a;padding:10px 20px;display:flex;flex-wrap:wrap;gap:6px}nav a{color:#cbd0da;text-decoration:none;padding:6px 12px;border-radius:8px}nav a.on{background:#2f6bff;color:#fff}
.b{background:#fff;border-radius:12px;padding:20px;margin-bottom:18px;box-shadow:0 1px 3px #0001}label{display:block;font-weight:600;margin:12px 0 4px;font-size:13px}
input,textarea{width:100%;padding:10px;border:1px solid #d5d9e2;border-radius:8px;font:inherit}button,.btn{background:#2f6bff;color:#fff;border:0;padding:10px 18px;border-radius:8px;cursor:pointer;text-decoration:none;font:inherit;display:inline-block}
.d{background:#e5484d}.r{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #eee}.r a{color:#2f6bff}small{color:#777}</style>';

if (empty($_SESSION['a'])) {
    $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (password_verify($_POST['pw'] ?? '', settings()['admin_hash'] ?? '')) {
            session_regenerate_id(true); $_SESSION['a'] = 1; $_SESSION['t'] = bin2hex(random_bytes(16)); header('Location: ./'); exit;
        }
        $err = 'Wrong password'; sleep(1);
    }
    echo "<!doctype html>$css<div class='w' style='max-width:340px;margin-top:80px'><form method='post' class='b'><h3>Admin login</h3><input type='password' name='pw' placeholder='Password' required autofocus><br><br><button>Login</button><p style='color:#e5484d'>$err</p></form></div>";
    exit;
}
$T = $_SESSION['t'];
$tab = $_GET['s'] ?? 'settings';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($T, $_POST['t'] ?? '')) die('Bad token');
    $db = db();
    if ($tab === 'settings') {
        $q = $db->prepare('INSERT INTO settings(k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)');
        foreach (SETTINGS as $k => [$l, $t]) {
            if ($t === 'file') { if ($u = upload($_FILES[$k] ?? [])) $q->execute([$k, $u]); }
            else $q->execute([$k, trim($_POST[$k] ?? '')]);
        }
        if (strlen($_POST['new_pw'] ?? '') >= 8) $q->execute(['admin_hash', password_hash($_POST['new_pw'], PASSWORD_DEFAULT)]);
        $msg = 'Saved ✅';
    } elseif (isset(SECTIONS[$tab])) {
        if (isset($_POST['del'])) {
            $db->prepare('DELETE FROM items WHERE id=? AND section=?')->execute([(int)$_POST['del'], $tab]); $msg = 'Deleted';
        } else {
            $id = (int)($_POST['id'] ?? 0); $old = [];
            if ($id) { $o = $db->prepare('SELECT * FROM items WHERE id=? AND section=?'); $o->execute([$id, $tab]); $old = $o->fetch() ?: []; }
            $v = ['title' => '', 'subtitle' => '', 'body' => '', 'tags' => '', 'meta' => '', 'link' => '', 'image' => ''];
            foreach (SECTIONS[$tab][1] as $k => [$l, $t])
                $v[$k] = $t === 'file' ? (upload($_FILES[$k] ?? []) ?: ($old[$k] ?? '')) : trim($_POST[$k] ?? '');
            $sort = (int)($_POST['sort'] ?? 0); $vis = isset($_POST['visible']) ? 1 : 0;
            if ($id) $db->prepare('UPDATE items SET title=?,subtitle=?,body=?,tags=?,meta=?,link=?,image=?,sort=?,visible=? WHERE id=? AND section=?')
                ->execute([...array_values($v), $sort, $vis, $id, $tab]);
            else $db->prepare('INSERT INTO items(title,subtitle,body,tags,meta,link,image,sort,visible,section) VALUES(?,?,?,?,?,?,?,?,?,?)')
                ->execute([...array_values($v), $sort, $vis, $tab]);
            $msg = 'Saved ✅';
        }
    }
}

function field($k, $l, $t, $val) {
    echo "<label>" . e($l) . "</label>";
    if ($t === 'area') echo "<textarea name='$k' rows='5'>" . e($val) . "</textarea>";
    elseif ($t === 'file') echo "<input type='file' name='$k' accept='image/*,video/mp4'>" . ($val ? "<small>Current: " . e($val) . " (upload to replace)</small>" : '');
    else echo "<input name='$k' value='" . e($val) . "'>";
}

echo "<!doctype html>$css<nav>";
$tabs = ['settings' => 'Main info'] + array_map(fn($s) => $s[0], SECTIONS) + ['messages' => 'Messages'];
foreach ($tabs as $k => $l) echo "<a class='" . ($tab === $k ? 'on' : '') . "' href='?s=$k'>" . e($l) . "</a>";
echo "<a href='../' target='_blank' style='margin-left:auto'>View site ↗</a><a href='?logout=1'>Logout</a></nav><div class='w'>";
if ($msg) echo "<div class='b'>$msg</div>";

if ($tab === 'settings') {
    $S = settings();
    echo "<form method='post' enctype='multipart/form-data' class='b'><input type='hidden' name='t' value='$T'><h3>Main info</h3>";
    foreach (SETTINGS as $k => [$l, $t]) field($k, $l, $t, $t === 'file' ? ($S[$k] ?? '') : ($S[$k] ?? ''));
    echo "<label>New admin password (leave blank to keep)</label><input type='password' name='new_pw'><br><button>Save</button></form>";
} elseif ($tab === 'messages') {
    foreach (db()->query('SELECT * FROM messages ORDER BY id DESC LIMIT 200') as $m)
        echo "<div class='b'><b>" . e($m['name']) . "</b> · <a href='mailto:" . e($m['email']) . "'>" . e($m['email']) . "</a> · " . e($m['phone']) . " <small>" . e($m['created']) . "</small><p>" . nl2br(e($m['msg'])) . "</p></div>";
} elseif (isset(SECTIONS[$tab])) {
    $edit = [];
    if (isset($_GET['edit'])) { $o = db()->prepare('SELECT * FROM items WHERE id=? AND section=?'); $o->execute([(int)$_GET['edit'], $tab]); $edit = $o->fetch() ?: []; }
    echo "<form method='post' enctype='multipart/form-data' class='b'><input type='hidden' name='t' value='$T'><input type='hidden' name='id' value='" . ($edit['id'] ?? 0) . "'>";
    echo "<h3>" . ($edit ? 'Edit item' : 'Add new item') . "</h3>";
    foreach (SECTIONS[$tab][1] as $k => [$l, $t]) field($k, $l, $t, $edit[$k] ?? '');
    echo "<label>Order (small number = first)</label><input type='number' name='sort' value='" . ($edit['sort'] ?? 0) . "'>";
    echo "<label><input type='checkbox' name='visible' style='width:auto' " . (($edit['visible'] ?? 1) ? 'checked' : '') . "> Visible on site</label><br><button>Save</button>";
    if ($edit) echo " <a class='btn' style='background:#888' href='?s=$tab'>Cancel</a>";
    echo "</form><div class='b'><h3>" . e(SECTIONS[$tab][0]) . "</h3>";
    foreach (items($tab, true) as $r)
        echo "<div class='r'><span>" . e($r['title']) . ($r['visible'] ? '' : ' <small>(hidden)</small>') . " <small>#" . $r['sort'] . "</small></span><span><a href='?s=$tab&edit=" . $r['id'] . "'>Edit</a> &nbsp; <form method='post' style='display:inline' onsubmit=\"return confirm('Delete?')\"><input type='hidden' name='t' value='$T'><input type='hidden' name='del' value='" . $r['id'] . "'><button class='d' style='padding:4px 10px'>Delete</button></form></span></div>";
    echo "</div>";
}
echo "</div>";
