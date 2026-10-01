# Menjalankan Tabung Yuk di Vercel dari HP

Kode tetap HTML + PHP + CSS tanpa database. Vercel memakai runtime komunitas `vercel-php@0.9.0` (PHP 8.5). File `api/index.php` menangani seluruh halaman dan aset; localhost tetap bisa memakai file PHP di folder utama.

## 1. Impor repository

1. Masuk ke https://vercel.com/new melalui browser HP.
2. Hubungkan akun GitHub dan pilih repository **alicee98/tabung-yuk**.
3. Tekan **Import**.
4. Gunakan **Framework Preset: Other**, **Root Directory: ./ (root repository)**.
5. Biarkan Build Command, Output Directory, dan Install Command pada pengaturan default, tanpa override. Tidak perlu memilih Next.js, Vite, atau mengisi folder output.
6. Node.js disetel ke **22.x** oleh `package.json`. Jika diminta di pengaturan proyek, gunakan 22.x.
7. Tekan **Deploy**.

## 2. Tambahkan kunci sesi (wajib, sekali saja)

Aplikasi tidak menyimpan kunci rahasia di repository. Jika `TABUNG_APP_KEY` belum ada, website menampilkan halaman **Satu pengaturan lagi**, bukan login yang rusak.

1. Buka website hasil deployment.
2. Pada halaman pengaturan awal, tekan **Buat kunci acak**, lalu salin nilai pada kolom kunci. Nilai dibuat di browser Anda dan tidak otomatis dikirim ke server.
3. Buka proyek Vercel → **Settings → Environment Variables**.
4. Tambahkan:
   - Name: `TABUNG_APP_KEY`
   - Value: kunci acak yang baru dibuat (64 karakter heksadesimal).
   - Environment: **Production** dan **Preview**.
5. Simpan. Kembali ke **Deployments**, pilih deployment terbaru, lalu **Redeploy** agar variabel diterapkan.
6. Setelah selesai, buka lagi domain website. Halaman login akan muncul.

Bisa juga menambahkan kunci sebelum deploy pertama jika sudah memiliki secret acak minimal 32 karakter. Jangan menaruh kunci di kode, README, screenshot publik, atau commit. Mengganti kunci akan mengakhiri seluruh sesi yang memakai kunci lama.

## 3. Login dan coba

- Username: `farisah01`
- Password: `tabung123`

Buat jadwal, buat deposit, periksa kode acak, konfirmasi simulasi, dan buka dashboard serta riwayat. Halaman bisa dibuka lewat HP. Navigasi di HP dapat digeser ke samping.

**Tidak ada pembayaran sungguhan.** Gambar QRIS ditampilkan untuk ilustrasi. Tombol konfirmasi adalah simulasi dan tidak memverifikasi rekening atau penyedia pembayaran.

## Cara sesi bekerja

- **Localhost:** session PHP biasa di server, sama seperti versi awal.
- **Vercel:** PHP tetap memakai `$_SESSION`, tetapi session handler menyimpan snapshot data dalam cookie terenkripsi AES-256-GCM. Kunci hanya ada di environment Vercel.
- Data tidak bergantung pada file `/tmp` server Vercel sehingga tetap terbaca ketika permintaan berikutnya dilayani instance lain.
- Cookie memakai HttpOnly, SameSite=Lax, dan Secure di Vercel/HTTPS. Halaman pribadi tidak di-cache.
- Cookie berlaku 4 jam sejak aktivitas terakhir. Keluar menghapus cookie dan data dari browser saat ini; data juga hilang jika cookie dihapus.
- Kapasitas demo Vercel: **10 tujuan dan 30 transaksi per sesi**, serta maksimum 8.400 karakter cookie data. Jika kapasitas cookie terlampaui karena teks panjang, perubahan terakhir ditolak dengan pesan yang jelas dan snapshot sebelumnya dipertahankan.
- Buka dan gunakan formulir berurutan dalam satu tab. Perubahan bersamaan dari beberapa tab dapat menimpa snapshot terakhir. Cookie lama yang disalin dapat diputar ulang selama masa berlakunya; ini bukan sistem autentikasi/keuangan produksi dan tidak mempunyai pencabutan sesi terpusat.
- Nominal dan kode tetap diproses PHP. Kode tidak berubah saat refresh. Konfirmasi ulang pada sesi terbaru tidak menggandakan saldo.
- Tidak ada database, Redis, atau layanan penyimpanan tambahan.

## Struktur dan keamanan routing

`vercel.json` mengarahkan semua URL ke `api/index.php`. Router hanya mengizinkan sembilan rute PHP aplikasi (termasuk pengarah dan logout), serta daftar aset yang eksplisit. URL seperti `/includes/app.php`, `/.env`, `/README.md`, atau `/tests/...` mengembalikan 404, bukan isi kode. Seluruh kode sumber tetap dapat dilihat melalui repository GitHub yang memang publik.

Gambar QRIS yang sudah diunggah dipertahankan. Router membaca tipe gambar sebenarnya sehingga file JPEG yang bernama `qris.png` tetap mendapat Content-Type yang sesuai.

## Pemecahan masalah

- **Satu pengaturan lagi / HTTP 503:** tambah `TABUNG_APP_KEY` minimal 32 karakter, pastikan environment sesuai, lalu Redeploy.
- **Runtime/Node tidak didukung:** pastikan Node.js 22.x dan runtime pada `vercel.json` tidak diubah.
- **Build tidak menemukan api/index.php:** pastikan Root Directory adalah root repository.
- **Kembali ke login:** periksa apakah cookie diizinkan, sesi tidak kedaluwarsa, dan kunci tidak berubah.
- **Kapasitas demo penuh:** gunakan catatan yang lebih pendek atau keluar untuk memulai sesi baru. Unduh/catat informasi yang diperlukan sebelum keluar.
- **QRIS tidak muncul:** pastikan `assets/qris.png` tersedia dan merupakan gambar PNG/JPEG yang valid.
- **Deployment gagal:** buka Build Logs di Vercel dan bagikan pesan errornya, tanpa menampilkan secret.

Referensi: https://github.com/vercel-community/php dan https://vercel.com/docs/git/vercel-for-github

## Pengujian pengembang

Jalankan `php -S localhost:8000 api/index.php`, lalu di terminal lain jalankan `python3 tests/smoke.py http://localhost:8000`. Test memakai cookie jar sendiri dan data simulasi. Untuk menguji driver Vercel di localhost, set `TABUNG_SESSION_DRIVER=cookie` dan `TABUNG_APP_KEY` pada environment server PHP sebelum memulai; jangan commit nilainya.

Validasi sebelum commit: sintaks seluruh PHP, alur aplikasi pada PHP 8.5, session cookie berpindah di antara dua runtime PHP independen, penolakan cookie yang diubah, rotasi kunci, perlindungan URL kode sumber, dan tipe gambar QRIS. Validasi ini belum merupakan konfirmasi deployment produksi di akun Vercel.
