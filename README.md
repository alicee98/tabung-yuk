# Tabung Yuk

**Sedikit jadi bukit.**

Tabung Yuk adalah aplikasi web untuk merencanakan tujuan tabungan, mencatat deposit, dan memantau progres menabung. Dikembangkan sebagai proyek lintas bidang Keuangan dan Informatika, aplikasi ini menggunakan HTML, PHP, CSS, dan JavaScript tanpa database.

[**Buka aplikasi**](https://tabung-yuk.vercel.app/)

> Aplikasi berjalan dalam mode simulasi. Saldo dan konfirmasi transaksi merupakan data latihan; tampilan QRIS tidak terhubung dengan layanan verifikasi pembayaran.

## Fitur

- Login dengan profil pengguna sesuai akun yang digunakan.
- Pembuatan tujuan tabungan dengan target, nominal rutin, serta jadwal harian atau mingguan.
- Deposit yang dikaitkan dengan tujuan tabungan.
- Nominal pembayaran dengan tambahan kode acak 001–999.
- Halaman QRIS dengan batas waktu, konfirmasi simulasi, dan pembatalan transaksi.
- Dashboard saldo dan progres tiap tujuan.
- Riwayat transaksi dengan filter status.
- Tampilan responsif untuk desktop dan perangkat seluler.

## Halaman aplikasi

| Halaman | Fungsi |
| --- | --- |
| Login | Autentikasi pengguna. |
| Beranda | Ringkasan tabungan dan akses menu utama. |
| Jadwal tabungan | Pengaturan tujuan, target, dan jadwal menabung. |
| Deposit | Pengisian nominal setoran dan tujuan tabungan. |
| Pembayaran | Detail nominal, gambar QRIS, dan status transaksi. |
| Dashboard | Ringkasan saldo, target, dan progres tabungan. |
| Riwayat | Daftar transaksi beserta statusnya. |

Alur utama: **Login → Buat tujuan → Deposit → Konfirmasi simulasi → Dashboard dan riwayat.**

## Teknologi

| Komponen | Teknologi |
| --- | --- |
| Antarmuka | HTML dan CSS |
| Logika aplikasi | PHP |
| Interaksi antarmuka | JavaScript |
| Penyimpanan sementara | Session PHP atau cookie terenkripsi |
| Hosting | Vercel dengan runtime komunitas `vercel-php` |

## Menjalankan secara lokal

Gunakan PHP 7.4 atau lebih baru; PHP 8.x direkomendasikan.

```sh
git clone https://github.com/alicee98/tabung-yuk.git
cd tabung-yuk
php -S localhost:8000
```

Buka **http://localhost:8000/**. Aplikasi juga dapat dijalankan melalui Apache/XAMPP tanpa MySQL. Akun percobaan dan profil pengguna didefinisikan di `includes/app.php`.

## Deployment

Repository menyediakan konfigurasi routing dan runtime PHP melalui `vercel.json` serta `api/index.php`.

Impor repository ke Vercel dengan preset **Other**, lalu tetapkan environment variable `TABUNG_APP_KEY` berupa kunci acak minimal 32 karakter pada environment deployment yang digunakan. Kunci ini digunakan untuk mengenkripsi session dan tidak boleh disimpan dalam repository. Daftar variabel tersedia di `.env.example`.

## Perilaku data dan transaksi

Kode acak dibuat saat transaksi baru disimpan. Contohnya, deposit **Rp5.000** dengan kode **123** menghasilkan total **Rp5.123**. Memuat ulang halaman tidak mengubah kode transaksi tersebut. Kode baru berbeda dari transaksi sebelumnya dalam session yang sama, tetapi kode historis dapat terulang.

Transaksi menunggu berlaku selama **30 menit**. Hanya transaksi yang dikonfirmasi berhasil dalam simulasi yang menambah saldo, termasuk nilai kode acaknya. Konfirmasi ulang transaksi yang sama tidak menggandakan saldo.

Pada localhost, data menggunakan session PHP di server. Pada Vercel, data menggunakan snapshot session dalam cookie terenkripsi, dengan batas **10 tujuan**, **30 transaksi**, dan masa aktif maksimal **4 jam tanpa aktivitas**. Data bersifat sementara dan dihapus saat keluar; tidak tersedia penyimpanan riwayat permanen.

Jadwal tabungan digunakan sebagai acuan menabung dan tidak menjalankan pendebitan otomatis atau pengingat terjadwal.

## Struktur kode

| Lokasi | Isi |
| --- | --- |
| `*.php` di direktori utama | Halaman aplikasi, pengalihan, dan logout. |
| `includes/` | Logika bersama, profil pengguna, dan pengelolaan session. |
| `assets/` | CSS, JavaScript, ikon, dan gambar QRIS. |
| `api/` | Entry point dan konfigurasi PHP untuk Vercel. |
| `tests/` | Skrip pengujian alur aplikasi. |
| `vercel.json` | Konfigurasi deployment dan routing. |

## Tim

Proyek Kelompok 5: **Farisah, Benaya, Andrian, Almi, dan Lutfi**.

## Lisensi

Lihat [LICENSE](LICENSE) untuk ketentuan GNU General Public License versi 3 yang disertakan dalam repository.
