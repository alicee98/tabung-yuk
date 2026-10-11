<?php
require __DIR__.'/includes/app.php';
if (!empty($_SESSION['user'])) go('beranda.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $username = trim((string)($_POST['username'] ?? ''));
 if (in_array($username, DEMO_USERNAMES, true) && hash_equals(DEMO_PASSWORD, (string)($_POST['password'] ?? ''))) {
  session_regenerate_id(true); $_SESSION['user'] = $username; $_SESSION['csrf'] = bin2hex(random_bytes(32)); go('beranda.php');
 }
 $error = 'Username atau password salah.';
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login | Tabung Yuk</title><link rel="icon" href="assets/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="assets/style.css"><script src="assets/app.js" defer></script><script defer src="https://cdn.vercel-insights.com/v1/script.js"></script></head><body class="login-body"><main class="login-layout"><section class="login-story"><a class="brand" href="login.php"><span class="brand-mark">ty.</span><span>Tabung Yuk<small>SEDIKIT JADI BUKIT</small></span></a><div class="story-content story-carousel" data-story-carousel role="region" aria-roledescription="karusel" aria-label="Langkah menabung" tabindex="0">
    <div class="story-slides" data-story-slides aria-live="off">
        <article class="story-slide is-active" data-story-slide role="group" aria-roledescription="slide" aria-label="1 dari 3">
            <span class="story-label">MULAI KEBIASAAN BAIKMU</span>
            <h1>Mimpi besar.<br> Mulai dari<br> <em>tabungan kecil.</em></h1>
            <p>Atur jadwal, sisihkan uang, dan lihat tujuanmu semakin dekat.</p>
        </article>
        <article class="story-slide" data-story-slide role="group" aria-roledescription="slide" aria-label="2 dari 3" hidden>
            <span class="story-label">RENCANAKAN KEBUTUHANMU</span>
            <h1>Satu jadwal.<br> Banyak tujuan.<br> <em>Langkah terarah.</em></h1>
            <p>Gabungkan beberapa barang dalam satu jadwal. Tentukan target dan nominal rutin yang sesuai kemampuanmu.</p>
        </article>
        <article class="story-slide" data-story-slide role="group" aria-roledescription="slide" aria-label="3 dari 3" hidden>
            <span class="story-label">LIHAT KEMAJUANMU</span>
            <h1>Sisihkan rutin.<br> Pantau progres.<br> <em>Wujudkan mimpi.</em></h1>
            <p>Catat deposit dan lihat perkiraan waktu tabungan selesai. Setiap setoran membawa tujuanmu semakin dekat.</p>
        </article>
    </div>
    <div class="story-progress" data-story-controls hidden>
        <div class="story-progress__heading"><span>LANGKAH MENUJU TUJUAN</span><b data-story-counter aria-hidden="true">01 / 03</b></div>
        <div class="story-tabs" role="group" aria-label="Pilih slide">
            <button type="button" data-story-select="0" aria-label="Slide 1: Mulai kebiasaan baikmu" aria-pressed="true"><span></span></button>
            <button type="button" data-story-select="1" aria-label="Slide 2: Rencanakan kebutuhanmu" aria-pressed="false"><span></span></button>
            <button type="button" data-story-select="2" aria-label="Slide 3: Lihat kemajuanmu" aria-pressed="false"><span></span></button>
        </div>
        <div class="story-navigation">
            <p>Rencanakan · Sisihkan · Wujudkan</p>
            <div class="story-buttons">
                <button type="button" data-story-prev aria-label="Slide sebelumnya">‹</button>
                <button type="button" data-story-pause aria-label="Jeda pergantian slide otomatis">Jeda</button>
                <button type="button" data-story-next aria-label="Slide berikutnya">›</button>
            </div>
        </div>
        <span class="sr-only" data-story-status role="status" aria-live="polite"></span>
    </div>
</div><small>Proyek lintas bidang · Keuangan & Informatika</small></section><section class="login-form-wrap"><div class="login-form"><span class="eyebrow">SELAMAT DATANG KEMBALI</span><h2>Masuk ke tabunganmu</h2><p>Langkah kecil hari ini, bekal untuk nanti.</p><?php if($error): ?><div class="alert error" role="alert"><?=e($error)?></div><?php endif ?><form method="post" action="login.php"><?=csrf()?><label for="username">Username</label><input id="username" name="username" autocomplete="username" placeholder="Masukkan username" required value="<?=old('username')?>"><label for="password">Password</label><div class="password-wrap"><input id="password" type="password" name="password" autocomplete="current-password" placeholder="Masukkan password" required><button type="button" id="toggle-password" aria-controls="password" aria-pressed="false">Lihat</button></div><button class="btn primary full" type="submit">Masuk</button></form><p class="small muted">Simulasi tanpa pembayaran nyata. Data disimpan sementara selama sesi dan dihapus saat keluar.</p><?php $mobileCompact = true; require __DIR__.'/includes/mobile_app.php'; ?></div><div class="login-credit">Kelompok 5 · Farisah, Benaya, Andrian, Almi, Lutfi</div></section></main></body></html>