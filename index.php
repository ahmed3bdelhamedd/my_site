<?php
require __DIR__ . '/lib.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msg'])) {
    header('Content-Type: application/json');
    $n = trim($_POST['name'] ?? ''); $em = trim($_POST['email'] ?? ''); $p = trim($_POST['phone'] ?? ''); $m = trim($_POST['msg']);
    if (!empty($_POST['website'])) { echo '{"ok":true}'; exit; } // honeypot
    if (!$n || !$m || !filter_var($em, FILTER_VALIDATE_EMAIL)) { echo '{"ok":false}'; exit; }
    db()->prepare('INSERT INTO messages(name,email,phone,msg) VALUES(?,?,?,?)')->execute([$n, $em, $p, $m]);
    if (TELEGRAM_TOKEN) {
        $c = curl_init('https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendMessage');
        curl_setopt_array($c, [CURLOPT_POST => 1, CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 8,
            CURLOPT_POSTFIELDS => ['chat_id' => TELEGRAM_CHAT, 'text' => "📩 $n\n$em\n$p\n\n$m"]]);
        curl_exec($c);
    }
    echo '{"ok":true}'; exit;
}

$S = settings();
$s = fn($k) => e($S[$k] ?? '');
$vsrc = !empty($S['video_file']) ? $S['video_file'] : (!empty($S['video_drive_url']) ? 'video.php' : '');
$secs = ['skills', 'projects', 'courses', 'certificates', 'experience'];
$data = []; foreach ($secs as $x) $data[$x] = items($x);
?><!doctype html>
<html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $s('name') ?> | <?= $s('badge') ?></title>
<meta name="description" content="<?= $s('tagline') ?>">
<style>
:root{--bg:#fff;--fg:#14161a;--mut:#5b6270;--card:#f4f5f8;--line:#e3e6ec;--ac:#2f6bff}
@media(prefers-color-scheme:dark){:root{--bg:#0e1013;--fg:#eceef2;--mut:#9aa1ae;--card:#171a20;--line:#262a33;--ac:#6d98ff}}
*{box-sizing:border-box}html{scroll-behavior:smooth}
body{margin:0;font:16px/1.65 system-ui,-apple-system,"Segoe UI",sans-serif;background:var(--bg);color:var(--fg)}
a{color:inherit}.w{max-width:1080px;margin:auto;padding:0 20px}
nav{position:sticky;top:0;background:color-mix(in srgb,var(--bg) 88%,transparent);backdrop-filter:blur(10px);border-bottom:1px solid var(--line);z-index:9}
nav .w{display:flex;justify-content:space-between;align-items:center;height:60px;gap:16px}
nav div{display:flex;gap:18px;overflow-x:auto;font-size:14px}nav div a{text-decoration:none;color:var(--mut);white-space:nowrap}
section{padding:72px 0}h2{font-size:clamp(26px,4vw,38px);margin:6px 0 28px;line-height:1.2}
.k{color:var(--ac);font-size:13px;font-weight:600;letter-spacing:.08em;text-transform:uppercase}
.hero{display:grid;grid-template-columns:1.2fr .8fr;gap:40px;align-items:center}
.hero h1{font-size:clamp(36px,6vw,60px);line-height:1.1;margin:8px 0}.hero p{color:var(--mut);max-width:560px}
.hero img{width:100%;border-radius:20px;aspect-ratio:1;object-fit:cover}
.btn{display:inline-block;padding:11px 22px;border-radius:10px;background:var(--ac);color:#fff;text-decoration:none;font-weight:600;border:0;font-size:15px;cursor:pointer}
.btn.o{background:none;color:var(--fg);border:1px solid var(--line)}
.vid{margin-top:48px;border-radius:18px;overflow:hidden;background:#000}.vid video{width:100%;display:block;max-height:80vh}
.g{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px}
.c{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px}.c h3{margin:0 0 6px;font-size:19px}.c p{color:var(--mut);margin:6px 0}
.c img{width:100%;border-radius:10px;margin-bottom:12px;display:block}
.t{display:inline-block;font-size:12.5px;padding:3px 10px;border-radius:99px;background:var(--bg);border:1px solid var(--line);margin:3px 4px 0 0}
.l{border-left:2px solid var(--line);padding-left:24px}.l .c{margin-bottom:16px}
ul.p{padding-left:20px;color:var(--mut)}
form{display:grid;gap:12px;max-width:560px}input,textarea{padding:12px;border-radius:10px;border:1px solid var(--line);background:var(--card);color:var(--fg);font:inherit;width:100%}
.ct{display:flex;flex-wrap:wrap;gap:10px 22px;margin-bottom:24px}.ct a{color:var(--ac)}
footer{border-top:1px solid var(--line);padding:28px 0;color:var(--mut);text-align:center;font-size:14px}
@media(max-width:760px){.hero{grid-template-columns:1fr}section{padding:48px 0}}
</style></head><body>
<nav><div class="w"><b><?= $s('name') ?></b><div>
<a href="#about">About</a>
<?php foreach (['skills' => 'Skills', 'projects' => 'Projects', 'courses' => 'Courses', 'certificates' => 'Certificates', 'experience' => 'Experience'] as $id => $lb) if ($data[$id]) echo "<a href='#$id'>$lb</a>"; ?>
<a href="#contact">Contact</a></div></div></nav>

<header class="w" style="padding-top:64px"><div class="hero"><div>
<span class="k"><?= $s('badge') ?></span><h1><?= $s('name') ?></h1>
<p style="font-size:20px;color:var(--fg)"><?= $s('tagline') ?></p><p><?= nl2br($s('intro')) ?></p>
<p><?php if ($S['cv_url'] ?? '') echo '<a class="btn" target="_blank" rel="noopener" href="' . $s('cv_url') . '">View CV</a> '; ?><a class="btn o" href="#contact">Get in touch</a></p>
</div><?php if ($S['photo'] ?? '') echo '<img src="' . $s('photo') . '" alt="' . $s('name') . '">'; ?></div>
<?php if ($vsrc): ?><div class="vid"><video controls playsinline preload="metadata" controlsList="nodownload" oncontextmenu="return false"
 <?= !empty($S['video_poster']) ? 'poster="' . $s('video_poster') . '"' : '' ?>><source src="<?= e($vsrc) ?>" type="video/mp4"></video></div><?php endif; ?>
</header>

<section id="about"><div class="w"><span class="k">About me</span><h2><?= $s('about_title') ?></h2>
<?php foreach (preg_split('/\n\s*\n/', $S['about_text'] ?? '') as $p) if (trim($p)) echo '<p>' . nl2br(e($p)) . '</p>'; ?>
<ul class="p"><?php foreach (array_filter(array_map('trim', explode("\n", $S['about_points'] ?? ''))) as $pt) echo '<li>' . e($pt) . '</li>'; ?></ul></div></section>

<?php if ($data['skills']): ?><section id="skills"><div class="w"><span class="k">Expertise</span><h2>Skills</h2><div class="g">
<?php foreach ($data['skills'] as $r): ?><div class="c"><h3><?= e($r['title']) ?></h3><?php foreach (tags($r['tags']) as $t) echo '<span class="t">' . e($t) . '</span>'; ?></div><?php endforeach; ?></div></div></section><?php endif; ?>

<?php if ($data['projects']): ?><section id="projects"><div class="w"><span class="k">Portfolio</span><h2>Projects</h2><div class="g">
<?php foreach ($data['projects'] as $r): ?><div class="c"><?php if ($r['image']) echo '<img loading="lazy" src="' . e($r['image']) . '" alt="">'; ?>
<h3><?= e($r['title']) ?></h3><p><?= nl2br(e($r['body'])) ?></p><?php foreach (tags($r['tags']) as $t) echo '<span class="t">' . e($t) . '</span>'; ?>
<?php if ($r['link']) echo '<p><a href="' . e($r['link']) . '" target="_blank" rel="noopener">View →</a></p>'; ?></div><?php endforeach; ?></div></div></section><?php endif; ?>

<?php if ($data['courses']): ?><section id="courses"><div class="w"><span class="k">Learn with me</span><h2>Courses</h2><div class="g">
<?php foreach ($data['courses'] as $r): ?><div class="c"><span class="t"><?= e($r['subtitle']) ?></span><h3 style="margin-top:10px"><?= e($r['title']) ?></h3><p><?= nl2br(e($r['body'])) ?></p>
<a href="mailto:<?= $s('email') ?>?subject=<?= rawurlencode('Course enquiry: ' . $r['title']) ?>">Ask about this course →</a></div><?php endforeach; ?></div></div></section><?php endif; ?>

<?php if ($data['certificates']): ?><section id="certificates"><div class="w"><span class="k">Credentials</span><h2>Certificates</h2><div class="g">
<?php foreach ($data['certificates'] as $r): ?><div class="c"><?php if ($r['image']) echo '<img loading="lazy" src="' . e($r['image']) . '" alt="">'; ?><h3><?= e($r['title']) ?></h3><p><?= e($r['subtitle']) ?></p></div><?php endforeach; ?></div></div></section><?php endif; ?>

<?php if ($data['experience']): ?><section id="experience"><div class="w"><span class="k">Background</span><h2>Experience & education</h2><div class="l">
<?php foreach ($data['experience'] as $r): ?><div class="c"><h3><?= e($r['title']) ?></h3><p><b><?= e($r['subtitle']) ?></b> · <?= e($r['meta']) ?></p><p><?= nl2br(e($r['body'])) ?></p>
<?php foreach (tags($r['tags']) as $t) echo '<span class="t">' . e($t) . '</span>'; ?></div><?php endforeach; ?></div></div></section><?php endif; ?>

<section id="contact"><div class="w"><span class="k">Get in touch</span><h2>Let's start a conversation</h2>
<div class="ct"><?php
if ($S['email'] ?? '') echo '<a href="mailto:' . $s('email') . '">' . $s('email') . '</a>';
if ($S['phone'] ?? '') echo '<a href="tel:' . $s('phone') . '">' . $s('phone') . '</a>';
if ($S['whatsapp'] ?? '') echo '<a target="_blank" rel="noopener" href="https://wa.me/' . preg_replace('/\D/', '', $S['whatsapp']) . '">WhatsApp</a>';
if ($S['linkedin'] ?? '') echo '<a target="_blank" rel="noopener" href="' . $s('linkedin') . '">LinkedIn</a>';
if ($S['behance'] ?? '') echo '<a target="_blank" rel="noopener" href="' . $s('behance') . '">Behance</a>';
?></div>
<form id="f"><input name="name" placeholder="Name" required><input name="email" type="email" placeholder="Email" required><input name="phone" placeholder="Phone (optional)">
<input name="website" style="display:none" tabindex="-1" autocomplete="off"><textarea name="msg" rows="5" placeholder="Message" required></textarea>
<button class="btn">Send message</button><small id="r"></small></form></div></section>
<footer>© <?= date('Y') ?> <?= $s('name') ?> · <?= $s('location') ?></footer>
<script>
document.getElementById('f').onsubmit=async e=>{e.preventDefault();const r=document.getElementById('r');
try{const j=await (await fetch('index.php',{method:'POST',body:new FormData(e.target)})).json();
r.textContent=j.ok?'Thank you! Your message was sent.':'Please check the fields.';if(j.ok)e.target.reset()}catch(x){r.textContent='Something went wrong.'}}
</script></body></html>
