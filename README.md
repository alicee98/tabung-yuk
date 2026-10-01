# Tabung Yuk

Aplikasi tabungan pelajar untuk proyek lintas bidang Keuangan dan Informatika.
Kelompok 5: Farisah, Benaya, Andrian, Almi, Lutfi (mengikuti contoh PDF).

HTML + PHP + CSS, dengan sedikit JavaScript untuk tombol nominal, tampilkan password, dan hitung mundur. Semua alur utama tetap berjalan tanpa JavaScript. Tidak memakai database, Composer, npm, framework, CDN, atau koneksi internet.

## Menjalankan dengan XAMPP (Windows)

1. Ekstrak ZIP. Pastikan folder aplikasi bernama `tabung-yuk`.
2. Salin folder tersebut ke `C:\xampp\htdocs\tabung-yuk`.
3. Di XAMPP Control Panel, klik **Start** pada **Apache**. MySQL tidak perlu dijalankan.
4. Buka `http://localhost/tabung-yuk/` di browser. Jika Apache memakai port lain, tambahkan port itu, misalnya `http://localhost:8080/tabung-yuk/`.
5. Masuk dengan akun demo di bawah.

Pastikan `index.php` berada langsung di `C:\xampp\htdocs\tabung-yuk\index.php`, bukan di folder bertingkat dua. Jangan membuka file PHP dengan klik dua kali atau memakai VS Code Live Server, karena PHP harus dijalankan melalui server PHP.

## Akun demo

- Username: `farisah01`
- Password: `tabung123`

Akun untuk tugas localhost, ditetapkan dalam `includes/app.php`. Ini bukan sistem akun untuk penggunaan publik.

## Alternatif: PHP bawaan

Gunakan PHP 7.4 atau lebih baru (disarankan PHP 8.x), tanpa ekstensi tambahan. Dari terminal di dalam folder `tabung-yuk`, jalankan:

```sh
php -S localhost:8000
```

Buka `http://localhost:8000/`. Hentikan server dengan Ctrl+C.

## Tujuh halaman aplikasi

| Halaman | File | Fungsi |
|---|---|---|
| 1. Login | `login.php` | Username, password, tombol masuk, validasi akun demo. |
| 2. Beranda | `beranda.php` | Sambutan, saldo ringkas, akses menu, dan jadwal terdekat. |
| 3. Pembuat jadwal tabungan | `jadwal.php` | Membuat tujuan, target, nominal rutin, frekuensi, tanggal mulai; daftar jadwal dan progres. |
| 4. Deposit | `deposit.php` | Memilih tujuan, nominal, dan keterangan deposit. |
| 5. Pembayaran QRIS | `pembayaran.php` | Kolom gambar QRIS, nominal + kode acak, batas waktu, konfirmasi simulasi atau batal. |
| 6. Dashboard | `dashboard.php` | Total tabungan, total target, progres tiap tujuan, transaksi terbaru. |
| 7. Riwayat | `riwayat.php` | Semua deposit, filter status, dan tautan detail. |

`index.php` mengarahkan pengguna ke login/beranda. `logout.php` memproses tombol keluar.

## Alur demonstrasi

1. Login menggunakan akun demo.
2. Pilih **Jadwal tabungan**, isi contoh: “Beli sepatu”, target `500000`, nominal rutin `5000`, frekuensi harian, tanggal mulai hari ini. Klik **Simpan jadwal**.
3. Pilih **Deposit**, pilih tujuan tadi, isi nominal `5000`, lalu klik **Buat pembayaran**.
4. Halaman pembayaran menampilkan angka acak. Contoh: Rp5.000 + 123 = Rp5.123. Angka di aplikasi akan bervariasi.
5. Muat ulang: angka dan ID transaksi tetap sama.
6. Klik **Simulasikan pembayaran berhasil**. Total pembayaran termasuk kode akan masuk ke saldo sekali saja.
7. Buka **Dashboard** dan **Riwayat** untuk melihat hasil.
8. Buat deposit baru untuk melihat kode acak baru. Coba pembatalan dan filter riwayat.

## Sistem kode acak

- Nominal adalah bilangan bulat positif (Rp1–Rp1.000.000.000).
- Kode diambil menggunakan `random_int()` dari 1–999, ditampilkan sebagai 001–999.
- Total dihitung dengan penjumlahan, bukan menggabungkan string: `5000 + 123 = 5123`.
- Kode baru berbeda dari transaksi tepat sebelumnya. Total tidak bertabrakan dengan transaksi yang masih menunggu di sesi yang sama. Kode historis tetap dapat terulang karena rentang angka terbatas.
- Kode dan total disimpan di session saat transaksi dibuat. Refresh atau membuka kembali detail tidak mengacak ulang.
- Deposit berlaku 30 menit. PHP memeriksa waktu kedaluwarsa pada setiap permintaan; hitung mundur browser hanya pelengkap UI.
- Status: menunggu, berhasil (simulasi), dibatalkan, atau kedaluwarsa.
- Hanya status berhasil yang masuk saldo. Seluruh total termasuk kode dikreditkan; tidak ada potongan/biaya tersembunyi.
- Konfirmasi transaksi yang sama tidak bisa menggandakan saldo. Token formulir mencegah pengiriman ganda dari formulir yang sama.
- Kode acak bukan verifikasi bahwa pembayaran nyata sudah diterima.

## Tempat gambar QRIS

Pada awalnya tersedia satu kolom gambar kosong bertuliskan **AREA GAMBAR QRIS**.

Untuk menggantinya:
1. Siapkan gambar PNG QRIS Anda.
2. Namai persis `qris.png` (huruf kecil).
3. Simpan di `tabung-yuk/assets/qris.png`.
4. Muat ulang halaman pembayaran. Gambar otomatis menggantikan placeholder.

Tidak perlu mengubah kode. File gambar belum disediakan karena pengguna akan memasukkan sendiri. Jangan hanya mengganti ekstensi JPEG menjadi PNG; ekspor/simpan sebagai PNG.

Aplikasi ini **simulasi tugas sekolah**. Menampilkan gambar QRIS tidak membuat integrasi pembayaran otomatis. Jangan transfer uang asli saat mencoba. Tombol simulasi menandai berhasil tanpa memeriksa bank atau penyedia pembayaran.

## Data tanpa database

Data tujuan, jadwal, dan transaksi disimpan di `$_SESSION` PHP. Refresh mempertahankan data selama session masih aktif. Tiap session browser terpisah. Data bersifat sementara; keluar menghapus data, dan sesi dapat hilang ketika cookie/session berakhir atau dibersihkan. Aplikasi tidak menyimpan arsip jangka panjang dan tidak mengirim pengingat terjadwal.

Jadwal harian/mingguan dihitung dari tanggal mulai. Jadwal bukan pendebitan otomatis. Nominal deposit boleh berbeda dari nominal rutin. Target yang tercapai tetap bisa menerima setoran tambahan dan progres visual dibatasi 100%, sementara saldo asli tetap ditampilkan utuh.

## Struktur folder

- `assets/style.css`: seluruh gaya UI responsif.
- `assets/app.js`: interaksi UI ringan.
- `assets/favicon.svg`: ikon tab.
- `assets/QRIS-PETUNJUK.txt`: petunjuk slot QRIS.
- `includes/app.php`: session, fungsi bantu, kode bersama, navigasi, akun demo.
- Tujuh file halaman PHP dan file pengarah/logout di folder utama.

## Jika ada kendala

- 404: periksa letak folder dan URL, serta port Apache.
- Kode PHP tampil sebagai teks: jalankan melalui Apache/PHP, bukan Live Server/file langsung.
- Apache tidak bisa menyala: pastikan port tidak dipakai aplikasi lain, atau pakai port alternatif sesuai pengaturan XAMPP.
- Pesan sesi formulir tidak valid: muat ulang halaman dan kirim ulang formulir. Aktifkan cookie browser.
- Jadwal atau transaksi hilang setelah keluar: ini perilaku session yang memang digunakan untuk tugas tanpa database.
- Tidak bisa deposit: buat jadwal dahulu; semua deposit wajib terkait dengan sebuah tujuan.
