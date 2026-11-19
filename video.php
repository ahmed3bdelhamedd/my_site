<?php
// بيسحب الفيديو من درايف ويعرضه من موقعك، فالزائر مش بيشوف أي حاجة اسمها Drive
require __DIR__ . '/lib.php';
$url = settings()['video_drive_url'] ?? '';
if (!preg_match('~/d/([\w-]{20,})|[?&]id=([\w-]{20,})~', $url, $m)) { http_response_code(404); exit; }
$id = $m[1] ?: $m[2];
set_time_limit(0);
$hd = []; $code = 200; $sent = false;
$ch = curl_init('https://drive.usercontent.google.com/download?export=download&confirm=t&id=' . $id);
$opt = [
    CURLOPT_FOLLOWLOCATION => true, CURLOPT_USERAGENT => 'Mozilla/5.0', CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_HEADERFUNCTION => function ($c, $l) use (&$hd, &$code) {
        if (stripos($l, 'HTTP/') === 0) { preg_match('~ (\d{3})~', $l, $x); $code = (int)$x[1]; $hd = []; }
        elseif (strpos($l, ':')) { [$k, $v] = explode(':', $l, 2); $hd[strtolower(trim($k))] = trim($v); }
        return strlen($l);
    },
    CURLOPT_WRITEFUNCTION => function ($c, $d) use (&$hd, &$code, &$sent) {
        if (!$sent) {
            $sent = true;
            if ($code >= 400 || stripos($hd['content-type'] ?? '', 'text/html') === 0) { http_response_code(502); return 0; }
            http_response_code($code);
            foreach (['content-type', 'content-length', 'content-range'] as $k) if (isset($hd[$k])) header($k . ': ' . $hd[$k]);
            header('Accept-Ranges: bytes');
            header('Cache-Control: public, max-age=86400');
            while (ob_get_level()) ob_end_clean();
        }
        echo $d; flush();
        return strlen($d);
    },
];
if (!empty($_SERVER['HTTP_RANGE'])) $opt[CURLOPT_HTTPHEADER] = ['Range: ' . $_SERVER['HTTP_RANGE']];
curl_setopt_array($ch, $opt);
curl_exec($ch);
