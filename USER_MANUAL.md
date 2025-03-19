# Sistem Absensi dan Penggajian - User Manual

## Daftar Isi
1. [Pendahuluan](#pendahuluan)
2. [Instalasi](#instalasi)
3. [Login dan Logout](#login-dan-logout)
4. [Dashboard](#dashboard)
5. [Absensi](#absensi)
6. [Gaji](#gaji)
7. [Laporan](#laporan)
8. [Pengaturan](#pengaturan)
   - [Pengguna](#pengguna)
   - [Tarif](#tarif)
   - [Pengaturan Aplikasi](#pengaturan-aplikasi)
   - [Backup & Restore](#backup--restore)
9. [Profil](#profil)
10. [Bantuan](#bantuan)

## Pendahuluan

Sistem Absensi dan Penggajian adalah aplikasi berbasis web yang dirancang untuk memudahkan pengelolaan absensi karyawan dan perhitungan gaji. Aplikasi ini memiliki dua jenis pengguna:

- **Admin** - Memiliki akses penuh ke semua fitur aplikasi, termasuk manajemen pengguna, tarif, dan penggajian.
- **Karyawan** - Dapat melakukan absensi (check-in dan check-out), melihat riwayat absensi, dan melihat riwayat gaji.

## Instalasi

Untuk menginstal aplikasi, ikuti langkah-langkah berikut:

1. Pastikan server web (Apache/Nginx) dan PHP (versi 7.4 atau lebih tinggi) telah terinstal.
2. Pastikan MySQL/MariaDB telah terinstal.
3. Salin semua file aplikasi ke direktori web server.
4. Buat database baru di MySQL.
5. Import file `database.sql` ke database yang telah dibuat.
6. Salin file `.env.example` menjadi `.env`.
7. Edit file `.env` dan sesuaikan dengan konfigurasi database Anda.
8. Akses aplikasi melalui browser.
9. Login menggunakan akun default:
   - Username: admin
   - Password: admin123

## Login dan Logout

### Login
1. Buka aplikasi melalui browser.
2. Masukkan username dan password.
3. Klik tombol "Login".

### Logout
1. Klik menu dropdown dengan nama pengguna di pojok kanan atas.
2. Pilih "Logout".

## Dashboard

Dashboard menampilkan ringkasan informasi penting, seperti:

- Status absensi hari ini
- Tombol check-in dan check-out
- Ringkasan absensi bulan ini
- Ringkasan gaji bulan ini (jika sudah dihitung)

### Fitur Dashboard untuk Admin
- Jumlah total karyawan
- Jumlah karyawan yang sudah absen hari ini
- Ringkasan gaji yang sudah dihitung bulan ini
- Grafik absensi mingguan

### Fitur Dashboard untuk Karyawan
- Status absensi hari ini
- Tombol check-in dan check-out
- Riwayat absensi terbaru
- Informasi gaji terbaru (jika sudah dihitung)

## Absensi

### Melakukan Check-in
1. Dari Dashboard, klik tombol "Check-in".
2. Sistem akan mencatat waktu masuk Anda.

### Melakukan Check-out
1. Dari Dashboard, klik tombol "Check-out".
2. Sistem akan mencatat waktu keluar Anda.

### Melihat Riwayat Absensi
1. Klik menu "Absensi" di sidebar.
2. Gunakan filter untuk mencari absensi berdasarkan tanggal atau status.

### Fitur Absensi untuk Admin
1. Melihat absensi semua karyawan.
2. Filter absensi berdasarkan tanggal, karyawan, atau status.
3. Mengedit absensi karyawan jika diperlukan.

## Gaji

### Fitur Gaji untuk Admin
1. Menghitung gaji karyawan berdasarkan absensi.
2. Melihat detail gaji, termasuk rincian jam kerja dan perhitungan gaji.
3. Memfinalisasi gaji agar tidak dapat diubah lagi.
4. Menghapus gaji yang masih berstatus draft.

### Menghitung Gaji (Admin)
1. Klik menu "Gaji" di sidebar.
2. Klik tombol "Hitung Gaji".
3. Pilih karyawan, periode awal, dan periode akhir.
4. Klik tombol "Hitung".

### Melihat Detail Gaji
1. Klik menu "Gaji" di sidebar.
2. Klik tombol "Detail" pada baris gaji yang ingin dilihat.

### Memfinalisasi Gaji (Admin)
1. Klik menu "Gaji" di sidebar.
2. Klik tombol "Detail" pada baris gaji yang ingin difinalisasi.
3. Klik tombol "Finalisasi Gaji".
4. Konfirmasi finalisasi gaji.

## Laporan

### Melihat Laporan Absensi (Admin)
1. Klik menu "Laporan" di sidebar.
2. Pilih tab "Laporan Absensi".
3. Gunakan filter untuk memilih bulan, tahun, dan karyawan (opsional).
4. Klik tombol "Filter".

### Melihat Laporan Gaji (Admin)
1. Klik menu "Laporan" di sidebar.
2. Pilih tab "Laporan Gaji".
3. Gunakan filter untuk memilih bulan, tahun, karyawan (opsional), dan status (opsional).
4. Klik tombol "Filter".

### Mencetak Laporan
1. Tampilkan laporan yang ingin dicetak.
2. Klik tombol "Cetak Laporan".
3. Gunakan fungsi cetak browser untuk mencetak laporan.

### Mengekspor Laporan
1. Tampilkan laporan yang ingin diekspor.
2. Klik tombol "Export CSV".
3. File CSV akan diunduh.

## Pengaturan

### Pengguna

#### Menambah Pengguna (Admin)
1. Klik menu "Pengaturan" di sidebar, lalu pilih "Kelola Pengguna".
2. Klik tombol "Tambah Pengguna".
3. Isi formulir dengan data pengguna.
4. Pilih role pengguna (Admin atau Karyawan).
5. Klik tombol "Simpan".

#### Mengedit Pengguna (Admin)
1. Klik menu "Pengaturan" di sidebar, lalu pilih "Kelola Pengguna".
2. Klik tombol "Edit" pada baris pengguna yang ingin diedit.
3. Ubah data pengguna sesuai kebutuhan.
4. Klik tombol "Update".

#### Menghapus Pengguna (Admin)
1. Klik menu "Pengaturan" di sidebar, lalu pilih "Kelola Pengguna".
2. Klik tombol "Hapus" pada baris pengguna yang ingin dihapus.
3. Konfirmasi penghapusan pengguna.

### Tarif

#### Mengubah Tarif (Admin)
1. Klik menu "Pengaturan" di sidebar, lalu pilih "Kelola Tarif".
2. Isi formulir dengan tarif baru.
3. Klik tombol "Simpan Tarif".

#### Melihat Riwayat Tarif (Admin)
1. Klik menu "Pengaturan" di sidebar, lalu pilih "Kelola Tarif".
2. Lihat tabel "Riwayat Tarif" di bagian kanan halaman.

### Pengaturan Aplikasi

#### Mengubah Pengaturan Aplikasi (Admin)
1. Klik menu "Pengaturan" di sidebar, lalu pilih "Pengaturan Aplikasi".
2. Ubah pengaturan sesuai kebutuhan.
3. Klik tombol "Simpan Pengaturan".

#### Pengaturan yang Tersedia
- **Nama Aplikasi** - Nama aplikasi yang akan ditampilkan di header.
- **Nama Perusahaan** - Nama perusahaan yang akan ditampilkan di laporan.
- **Alamat Perusahaan** - Alamat perusahaan yang akan ditampilkan di laporan.
- **Telepon Perusahaan** - Nomor telepon perusahaan yang akan ditampilkan di laporan.
- **Email Perusahaan** - Email perusahaan yang akan ditampilkan di laporan.
- **Jam Mulai Kerja** - Jam mulai kerja normal.
- **Jam Selesai Kerja** - Jam selesai kerja normal.
- **Izinkan Absensi di Akhir Pekan** - Jika dicentang, karyawan dapat melakukan absensi di hari Sabtu dan Minggu.

### Backup & Restore

#### Membuat Backup (Admin)
1. Klik menu "Pengaturan" di sidebar, lalu pilih "Backup & Restore".
2. Klik tombol "Buat Backup Sekarang".
3. File backup akan dibuat dan ditampilkan di daftar file backup.

#### Mengunduh Backup (Admin)
1. Klik menu "Pengaturan" di sidebar, lalu pilih "Backup & Restore".
2. Cari file backup yang ingin diunduh di daftar file backup.
3. Klik tombol "Unduh" pada baris file backup tersebut.

#### Menghapus Backup (Admin)
1. Klik menu "Pengaturan" di sidebar, lalu pilih "Backup & Restore".
2. Cari file backup yang ingin dihapus di daftar file backup.
3. Klik tombol "Hapus" pada baris file backup tersebut.
4. Konfirmasi penghapusan file backup.

#### Merestore Database (Admin)
1. Klik menu "Pengaturan" di sidebar, lalu pilih "Backup & Restore".
2. Di bagian "Restore Database", klik tombol "Choose File".
3. Pilih file backup yang ingin direstore.
4. Klik tombol "Restore Database".
5. Konfirmasi restore database.

## Profil

### Melihat Profil
1. Klik menu dropdown dengan nama pengguna di pojok kanan atas.
2. Pilih "Profil".

### Mengubah Profil
1. Klik menu dropdown dengan nama pengguna di pojok kanan atas.
2. Pilih "Profil".
3. Ubah data profil sesuai kebutuhan.
4. Klik tombol "Simpan Perubahan".

### Mengubah Password
1. Klik menu dropdown dengan nama pengguna di pojok kanan atas.
2. Pilih "Profil".
3. Isi bagian "Ubah Password" dengan password saat ini dan password baru.
4. Klik tombol "Simpan Perubahan".

## Bantuan

Halaman bantuan menyediakan panduan penggunaan aplikasi untuk setiap fitur. Untuk mengakses bantuan:

1. Klik menu "Bantuan" di pojok kanan atas.
2. Pilih topik bantuan yang ingin dilihat.

---

© <?= date('Y') ?> Sistem Absensi dan Penggajian. All rights reserved.
