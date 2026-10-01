<?php
// Single Vercel entry point. Never let Vercel serve PHP source as a static file.
declare(strict_types=1);
$root = dirname(__DIR__);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($path) ? $path : '/';
header('X-Content-Type-Options: nosniff');
// Only these exact public assets may be read; no arbitrary path/file access.
$assets = [
 '/assets/style.css' => 'text/css; charset=utf-8',
 '/assets/app.js' => 'application/javascript; charset=utf-8',
 '/assets/favicon.svg' => 'image/svg+xml',
 '/assets/qris.png' => 'image/png',
 '/assets/qris.jpg' => 'image/jpeg',
 '/assets/qris.jpeg' => 'image/jpeg',
];
if (isset($assets[$path]) && is_file($root . $path)) {
 if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) {
  http_response_code(405); header('Allow: GET, HEAD'); exit;
 }
 $mime = $assets[$path];
 if (strpos($path, '/assets/qris.') === 0) {
  $image = getimagesize($root . $path);
  if ($image && in_array($image['mime'], ['image/png', 'image/jpeg'], true)) $mime = $image['mime'];
 }
 header('Content-Type: ' . $mime);
 header('Cache-Control: public, max-age=300');
 if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') readfile($root . $path);
 exit;
}
$routes = ['/' => 'index.php', '/index.php' => 'index.php', '/login.php' => 'login.php',
 '/beranda.php' => 'beranda.php', '/jadwal.php' => 'jadwal.php', '/deposit.php' => 'deposit.php',
 '/pembayaran.php' => 'pembayaran.php', '/dashboard.php' => 'dashboard.php',
 '/riwayat.php' => 'riwayat.php', '/logout.php' => 'logout.php'];
if ($path === '/api/index.php') { header('Location: /'); exit; }
if (!isset($routes[$path])) {
 http_response_code(404); header('Content-Type: text/html; charset=utf-8');
 echo '<!doctype html><html lang="id"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Halaman tidak ditemukan</title><link rel="stylesheet" href="/assets/style.css"><main class="panel" style="max-width:540px;margin:10vh auto"><h1>Halaman tidak ditemukan</h1><p>Gunakan menu aplikasi untuk melanjutkan.</p><a class="btn primary" href="/">Kembali ke aplikasi</a></main></html>';
 exit;
}
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'POST'], true)) {
 http_response_code(405); header('Allow: GET, HEAD, POST'); exit;
}
require $root . '/' . $routes[$path];
