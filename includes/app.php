<?php
// Tabung Yuk: aplikasi belajar tanpa database. Data hanya disimpan dalam session.
declare(strict_types=1);
date_default_timezone_set('Asia/Jakarta');
// Buffer output so encrypted session cookies can be written before redirects/HTML.
ob_start();
$cloudSession = getenv('VERCEL') === '1' || getenv('TABUNG_SESSION_DRIVER') === 'cookie';
define('TABUNG_CLOUD_SESSION', $cloudSession);
define('MAX_GOALS', $cloudSession ? 10 : 50);
define('MAX_TRANSACTIONS', $cloudSession ? 30 : 1000);
$secureCookie = getenv('VERCEL') === '1' || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if ($cloudSession) {
    $secret = (string)getenv('TABUNG_APP_KEY');
    if (strlen($secret) < 32) { require __DIR__.'/setup.php'; exit; }
    require_once __DIR__.'/cookie_session.php';
    session_set_save_handler(new TabungCookieSession($secret, $secureCookie), true);
}
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params(['path'=>'/', 'secure'=>$secureCookie, 'httponly'=>true, 'samesite'=>'Lax']);
session_start();
register_shutdown_function(function (): void {
    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
});
header('Cache-Control: private, no-store');
header('Vercel-CDN-Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
const DEMO_USERNAME = 'farisah01';
const DEMO_PASSWORD = 'tabung123';
const MAX_AMOUNT = 1000000000;
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function rp($value): string { return 'Rp' . number_format((float)$value, 0, ',', '.'); }
function go(string $path): void { header('Location: ' . $path); exit; }
function csrf(): string { return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">'; }
function form_id(string $name): string { if (empty($_SESSION['forms'][$name])) $_SESSION['forms'][$name] = bin2hex(random_bytes(16)); return '<input type="hidden" name="form_id" value="' . e($_SESSION['forms'][$name]) . '">'; }
function valid_form(string $name): bool { return isset($_SESSION['forms'][$name]) && hash_equals($_SESSION['forms'][$name], (string)($_POST['form_id'] ?? '')); }
function notice(string $text, string $type = 'success'): void { $_SESSION['flash'] = [$text, $type]; }
function amount($input): ?int { if (!is_string($input) || !preg_match('/^[0-9]{1,10}$/D', $input)) return null; $n = (int)$input; return ($n > 0 && $n <= MAX_AMOUNT) ? $n : null; }
function date_ok(string $value): bool { $d = DateTimeImmutable::createFromFormat('!Y-m-d', $value); return $d !== false && $d->format('Y-m-d') === $value; }
function pretty_date(string $date): string { return date('d/m/Y', strtotime($date)); }
function saved(string $goal = ''): int { $n=0; foreach ($_SESSION['transactions'] as $t) if ($t['status'] === 'success' && ($goal === '' || $t['goal_id'] === $goal)) $n += $t['total']; return $n; }
function count_status(string $status): int { return count(array_filter($_SESSION['transactions'], function($t) use ($status) { return $t['status'] === $status; })); }
function goal_name(string $id): string { return $_SESSION['goals'][$id]['name'] ?? 'Tujuan tidak tersedia'; }
function next_due(array $g): string { $d = new DateTimeImmutable($g['start']); $today = new DateTimeImmutable('today'); $step = $g['frequency'] === 'Harian' ? '+1 day' : '+7 days'; while ($d < $today) $d = $d->modify($step); return $d->format('Y-m-d'); }
function status_label(string $status): string { return ['pending'=>'Menunggu pembayaran','success'=>'Berhasil (simulasi)','cancelled'=>'Dibatalkan','expired'=>'Kedaluwarsa'][$status] ?? $status; }
function badge(string $status): string { return '<span class="badge '.e($status).'">'.e(status_label($status)).'</span>'; }
function progress(array $g): float { return min(100, round(saved($g['id']) / $g['target'] * 100, 1)); }
function old(string $key, string $fallback=''): string { return e($_POST[$key] ?? $fallback); }
function icon(string $name): string {
 $paths = ['home'=>'<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>', 'calendar'=>'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18m-13 4h3m3 0h3"/>', 'wallet'=>'<path d="M20 8V5a2 2 0 0 0-2-2H5a3 3 0 0 0-3 3v13a2 2 0 0 0 2 2h16V8H5a2 2 0 0 1 0-4"/><path d="M20 12h-6v5h6"/>', 'chart'=>'<path d="M4 3v17h17M8 16v-5m5 5V7m5 9V3"/>', 'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>', 'logout'=>'<path d="M9 4H4v16h5m4-13 5 5-5 5m-5-5h10"/>', 'plus'=>'<path d="M12 5v14M5 12h14"/>', 'check'=>'<path d="m5 12 4 4L19 6"/>', 'target'=>'<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>'];
 return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['wallet']).'</svg>';
}
if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) { http_response_code(403); exit('Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.'); }
$_SESSION['goals'] = $_SESSION['goals'] ?? [];
$_SESSION['transactions'] = $_SESSION['transactions'] ?? [];
foreach ($_SESSION['transactions'] as &$t) if ($t['status'] === 'pending' && $t['expires'] <= time()) $t['status'] = 'expired';
unset($t);
function require_login(): void { if (empty($_SESSION['user'])) go('login.php'); }
function page_start(string $title, string $active, string $subtitle): void {
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?=e($title)?> | Tabung Yuk</title><meta name="theme-color" content="#282464"><link rel="icon" href="assets/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="assets/style.css"><script src="assets/app.js" defer></script></head><body><a class="skip" href="#main">Lewati navigasi</a><aside class="sidebar"><a class="brand" href="beranda.php"><span class="brand-mark">ty.</span><span>Tabung Yuk<small>SEDIKIT JADI BUKIT</small></span></a><div class="nav-label">MENU UTAMA</div><nav aria-label="Navigasi utama"><?php foreach (['beranda'=>['home','Beranda'],'jadwal'=>['calendar','Jadwal tabungan'],'deposit'=>['wallet','Deposit'],'dashboard'=>['chart','Dashboard'],'riwayat'=>['clock','Riwayat']] as $file=>$item): ?><a class="nav-item <?=$active===$file?'active':''?>" href="<?=$file?>.php" <?=$active===$file?'aria-current="page"':''?>><?=icon($item[0])?><?=$item[1]?></a><?php endforeach ?></nav><div class="sidebar-bottom"><div class="demo-tag">PROYEK SEKOLAH</div><p>Latihan menabung.<br>Langkah kecil, tujuan besar.</p><form action="logout.php" method="post"><?=csrf()?><button class="logout" type="submit"><?=icon('logout')?>Keluar</button></form></div></aside><div class="workspace"><header class="topbar"><span>Aplikasi tabungan pelajar <span class="pill">Mode simulasi</span></span><div class="user"><span class="avatar">F</span><span>Farisah<small>Anggota Kelompok 5</small></span></div></header><main id="main"><div class="page-heading"><div><div class="eyebrow">TABUNG YUK / <?=e(strtoupper($title))?></div><h1><?=e($title)?></h1><p><?=e($subtitle)?></p></div><span class="date"><?=date('d M Y')?></span></div><?php if(isset($_SESSION['flash'])): [$text,$type]=$_SESSION['flash']; unset($_SESSION['flash']); ?><div class="alert <?=e($type)?>" role="status"><?=e($text)?></div><?php endif ?><?php
}
function page_end(): void { ?><footer class="footer"><span>Tabung Yuk · Kelompok 5</span><span>Data sementara<?=TABUNG_CLOUD_SESSION ? ' · maks. 4 jam tanpa aktivitas' : ''?>. Keluar menghapus data.</span></footer></main></div></body></html><?php }
function errors(array $errors): void { if ($errors): ?><div class="alert error" role="alert"><strong>Periksa kembali isian Anda.</strong><ul><?php foreach($errors as $error): ?><li><?=e($error)?></li><?php endforeach ?></ul></div><?php endif; }
function empty_state(string $title, string $text, string $href, string $label): void { ?><div class="empty"><?=icon('target')?><h3><?=e($title)?></h3><p><?=e($text)?></p><a class="btn primary" href="<?=e($href)?>"><?=e($label)?></a></div><?php }
