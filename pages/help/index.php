<?php
// Get user role
$role = $_SESSION['role'] ?? '';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4">Bantuan Penggunaan</h2>
        
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <button class="nav-link active" id="v-pills-overview-tab" data-bs-toggle="pill" data-bs-target="#v-pills-overview" type="button" role="tab" aria-controls="v-pills-overview" aria-selected="true">
                                <i class="fas fa-info-circle me-2"></i> Gambaran Umum
                            </button>
                            
                            <button class="nav-link" id="v-pills-attendance-tab" data-bs-toggle="pill" data-bs-target="#v-pills-attendance" type="button" role="tab" aria-controls="v-pills-attendance" aria-selected="false">
                                <i class="fas fa-calendar-check me-2"></i> Absensi
                            </button>
                            
                            <?php if ($role === 'admin'): ?>
                                <button class="nav-link" id="v-pills-salary-tab" data-bs-toggle="pill" data-bs-target="#v-pills-salary" type="button" role="tab" aria-controls="v-pills-salary" aria-selected="false">
                                    <i class="fas fa-money-bill-wave me-2"></i> Penggajian
                                </button>
                                
                                <button class="nav-link" id="v-pills-users-tab" data-bs-toggle="pill" data-bs-target="#v-pills-users" type="button" role="tab" aria-controls="v-pills-users" aria-selected="false">
                                    <i class="fas fa-users me-2"></i> Manajemen Pengguna
                                </button>
                                
                                <button class="nav-link" id="v-pills-tariff-tab" data-bs-toggle="pill" data-bs-target="#v-pills-tariff" type="button" role="tab" aria-controls="v-pills-tariff" aria-selected="false">
                                    <i class="fas fa-tags me-2"></i> Manajemen Tarif
                                </button>
                                
                                <button class="nav-link" id="v-pills-reports-tab" data-bs-toggle="pill" data-bs-target="#v-pills-reports" type="button" role="tab" aria-controls="v-pills-reports" aria-selected="false">
                                    <i class="fas fa-chart-bar me-2"></i> Laporan
                                </button>
                                
                                <button class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" data-bs-target="#v-pills-settings" type="button" role="tab" aria-controls="v-pills-settings" aria-selected="false">
                                    <i class="fas fa-cog me-2"></i> Pengaturan
                                </button>
                                
                                <button class="nav-link" id="v-pills-backup-tab" data-bs-toggle="pill" data-bs-target="#v-pills-backup" type="button" role="tab" aria-controls="v-pills-backup" aria-selected="false">
                                    <i class="fas fa-database me-2"></i> Backup & Restore
                                </button>
                            <?php endif; ?>
                            
                            <button class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                <i class="fas fa-user me-2"></i> Profil
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-9">
                        <div class="tab-content" id="v-pills-tabContent">
                            <!-- Overview -->
                            <div class="tab-pane fade show active" id="v-pills-overview" role="tabpanel" aria-labelledby="v-pills-overview-tab">
                                <h4>Gambaran Umum Aplikasi</h4>
                                <hr>
                                
                                <p>Sistem Absensi dan Penggajian adalah aplikasi berbasis web yang dirancang untuk memudahkan pengelolaan absensi karyawan dan perhitungan gaji. Aplikasi ini memiliki dua jenis pengguna:</p>
                                
                                <ul>
                                    <li><strong>Admin</strong> - Memiliki akses penuh ke semua fitur aplikasi, termasuk manajemen pengguna, tarif, dan penggajian.</li>
                                    <li><strong>Karyawan</strong> - Dapat melakukan absensi (check-in dan check-out), melihat riwayat absensi, dan melihat riwayat gaji.</li>
                                </ul>
                                
                                <p>Fitur utama aplikasi ini meliputi:</p>
                                
                                <ol>
                                    <li><strong>Absensi</strong> - Pencatatan waktu masuk dan keluar karyawan.</li>
                                    <li><strong>Penggajian</strong> - Perhitungan gaji berdasarkan jam kerja normal dan lembur.</li>
                                    <li><strong>Laporan</strong> - Laporan absensi dan gaji yang dapat difilter berdasarkan periode dan karyawan.</li>
                                    <li><strong>Manajemen Pengguna</strong> - Pengelolaan data karyawan dan admin.</li>
                                    <li><strong>Manajemen Tarif</strong> - Pengaturan tarif jam kerja normal dan lembur.</li>
                                </ol>
                                
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i> Untuk informasi lebih detail tentang setiap fitur, silakan pilih menu bantuan yang sesuai di sebelah kiri.
                                </div>
                            </div>
                            
                            <!-- Attendance -->
                            <div class="tab-pane fade" id="v-pills-attendance" role="tabpanel" aria-labelledby="v-pills-attendance-tab">
                                <h4>Panduan Absensi</h4>
                                <hr>
                                
                                <h5>Melakukan Check-in</h5>
                                <p>Untuk melakukan check-in, ikuti langkah-langkah berikut:</p>
                                <ol>
                                    <li>Masuk ke halaman Dashboard.</li>
                                    <li>Klik tombol "Check-in" pada kartu Absensi Hari Ini.</li>
                                    <li>Sistem akan mencatat waktu masuk Anda.</li>
                                </ol>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Check-in hanya dapat dilakukan sekali dalam sehari.
                                </div>
                                
                                <h5>Melakukan Check-out</h5>
                                <p>Untuk melakukan check-out, ikuti langkah-langkah berikut:</p>
                                <ol>
                                    <li>Masuk ke halaman Dashboard.</li>
                                    <li>Klik tombol "Check-out" pada kartu Absensi Hari Ini.</li>
                                    <li>Sistem akan mencatat waktu keluar Anda.</li>
                                </ol>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Check-out hanya dapat dilakukan setelah melakukan check-in.
                                </div>
                                
                                <h5>Melihat Riwayat Absensi</h5>
                                <p>Untuk melihat riwayat absensi, ikuti langkah-langkah berikut:</p>
                                <ol>
                                    <li>Klik menu "Absensi" pada sidebar.</li>
                                    <li>Anda akan melihat daftar absensi Anda.</li>
                                    <li>Gunakan filter untuk mencari absensi berdasarkan tanggal atau status.</li>
                                </ol>
                                
                                <?php if ($role === 'admin'): ?>
                                    <h5>Melihat Absensi Karyawan (Admin)</h5>
                                    <p>Sebagai admin, Anda dapat melihat absensi semua karyawan:</p>
                                    <ol>
                                        <li>Klik menu "Absensi" pada sidebar.</li>
                                        <li>Anda akan melihat daftar absensi semua karyawan.</li>
                                        <li>Gunakan filter untuk mencari absensi berdasarkan tanggal, karyawan, atau status.</li>
                                    </ol>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($role === 'admin'): ?>
                                <!-- Salary -->
                                <div class="tab-pane fade" id="v-pills-salary" role="tabpanel" aria-labelledby="v-pills-salary-tab">
                                    <h4>Panduan Penggajian</h4>
                                    <hr>
                                    
                                    <h5>Menghitung Gaji</h5>
                                    <p>Untuk menghitung gaji karyawan, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Gaji" pada sidebar.</li>
                                        <li>Klik tombol "Hitung Gaji".</li>
                                        <li>Pilih karyawan, periode awal, dan periode akhir.</li>
                                        <li>Klik tombol "Hitung".</li>
                                        <li>Sistem akan menghitung gaji berdasarkan absensi karyawan pada periode tersebut.</li>
                                    </ol>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> Perhitungan gaji menggunakan tarif yang berlaku saat perhitungan dilakukan.
                                    </div>
                                    
                                    <h5>Melihat Detail Gaji</h5>
                                    <p>Untuk melihat detail gaji, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Gaji" pada sidebar.</li>
                                        <li>Klik tombol "Detail" pada baris gaji yang ingin dilihat.</li>
                                        <li>Anda akan melihat detail gaji, termasuk rincian jam kerja dan perhitungan gaji.</li>
                                    </ol>
                                    
                                    <h5>Memfinalisasi Gaji</h5>
                                    <p>Setelah gaji dihitung, Anda perlu memfinalisasi gaji agar tidak dapat diubah lagi:</p>
                                    <ol>
                                        <li>Klik menu "Gaji" pada sidebar.</li>
                                        <li>Klik tombol "Detail" pada baris gaji yang ingin difinalisasi.</li>
                                        <li>Klik tombol "Finalisasi Gaji".</li>
                                        <li>Konfirmasi finalisasi gaji.</li>
                                    </ol>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Gaji yang sudah difinalisasi tidak dapat diubah atau dihapus.
                                    </div>
                                </div>
                                
                                <!-- Users Management -->
                                <div class="tab-pane fade" id="v-pills-users" role="tabpanel" aria-labelledby="v-pills-users-tab">
                                    <h4>Panduan Manajemen Pengguna</h4>
                                    <hr>
                                    
                                    <h5>Menambah Pengguna</h5>
                                    <p>Untuk menambah pengguna baru, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Pengguna" pada sidebar.</li>
                                        <li>Klik tombol "Tambah Pengguna".</li>
                                        <li>Isi formulir dengan data pengguna.</li>
                                        <li>Pilih role pengguna (Admin atau Karyawan).</li>
                                        <li>Klik tombol "Simpan".</li>
                                    </ol>
                                    
                                    <h5>Mengedit Pengguna</h5>
                                    <p>Untuk mengedit data pengguna, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Pengguna" pada sidebar.</li>
                                        <li>Klik tombol "Edit" pada baris pengguna yang ingin diedit.</li>
                                        <li>Ubah data pengguna sesuai kebutuhan.</li>
                                        <li>Klik tombol "Update".</li>
                                    </ol>
                                    
                                    <h5>Menghapus Pengguna</h5>
                                    <p>Untuk menghapus pengguna, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Pengguna" pada sidebar.</li>
                                        <li>Klik tombol "Hapus" pada baris pengguna yang ingin dihapus.</li>
                                        <li>Konfirmasi penghapusan pengguna.</li>
                                    </ol>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Anda tidak dapat menghapus akun Anda sendiri.
                                    </div>
                                </div>
                                
                                <!-- Tariff Management -->
                                <div class="tab-pane fade" id="v-pills-tariff" role="tabpanel" aria-labelledby="v-pills-tariff-tab">
                                    <h4>Panduan Manajemen Tarif</h4>
                                    <hr>
                                    
                                    <h5>Mengubah Tarif</h5>
                                    <p>Untuk mengubah tarif, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Tarif" pada sidebar.</li>
                                        <li>Isi formulir dengan tarif baru.</li>
                                        <li>Klik tombol "Simpan Tarif".</li>
                                    </ol>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> Tarif yang diubah akan berlaku untuk perhitungan gaji yang dilakukan setelah perubahan tarif.
                                    </div>
                                    
                                    <h5>Melihat Riwayat Tarif</h5>
                                    <p>Untuk melihat riwayat perubahan tarif, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Tarif" pada sidebar.</li>
                                        <li>Lihat tabel "Riwayat Tarif" di bagian kanan halaman.</li>
                                    </ol>
                                </div>
                                
                                <!-- Reports -->
                                <div class="tab-pane fade" id="v-pills-reports" role="tabpanel" aria-labelledby="v-pills-reports-tab">
                                    <h4>Panduan Laporan</h4>
                                    <hr>
                                    
                                    <h5>Melihat Laporan Absensi</h5>
                                    <p>Untuk melihat laporan absensi, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Laporan" pada sidebar.</li>
                                        <li>Pilih tab "Laporan Absensi".</li>
                                        <li>Pilih bulan, tahun, dan karyawan (opsional).</li>
                                        <li>Klik tombol "Filter".</li>
                                        <li>Laporan absensi akan ditampilkan.</li>
                                    </ol>
                                    
                                    <h5>Melihat Laporan Gaji</h5>
                                    <p>Untuk melihat laporan gaji, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Laporan" pada sidebar.</li>
                                        <li>Pilih tab "Laporan Gaji".</li>
                                        <li>Pilih bulan, tahun, karyawan (opsional), dan status (opsional).</li>
                                        <li>Klik tombol "Filter".</li>
                                        <li>Laporan gaji akan ditampilkan.</li>
                                    </ol>
                                    
                                    <h5>Mencetak Laporan</h5>
                                    <p>Untuk mencetak laporan, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Tampilkan laporan yang ingin dicetak.</li>
                                        <li>Klik tombol "Cetak Laporan".</li>
                                        <li>Halaman cetak akan terbuka.</li>
                                        <li>Gunakan fungsi cetak browser untuk mencetak laporan.</li>
                                    </ol>
                                    
                                    <h5>Mengekspor Laporan</h5>
                                    <p>Untuk mengekspor laporan ke CSV, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Tampilkan laporan yang ingin diekspor.</li>
                                        <li>Klik tombol "Export CSV".</li>
                                        <li>File CSV akan diunduh.</li>
                                    </ol>
                                </div>
                                
                                <!-- Settings -->
                                <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                                    <h4>Panduan Pengaturan</h4>
                                    <hr>
                                    
                                    <h5>Mengubah Pengaturan Aplikasi</h5>
                                    <p>Untuk mengubah pengaturan aplikasi, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Pengaturan" pada sidebar.</li>
                                        <li>Ubah pengaturan sesuai kebutuhan.</li>
                                        <li>Klik tombol "Simpan Pengaturan".</li>
                                    </ol>
                                    
                                    <h5>Pengaturan yang Tersedia</h5>
                                    <ul>
                                        <li><strong>Nama Aplikasi</strong> - Nama aplikasi yang akan ditampilkan di header.</li>
                                        <li><strong>Nama Perusahaan</strong> - Nama perusahaan yang akan ditampilkan di laporan.</li>
                                        <li><strong>Alamat Perusahaan</strong> - Alamat perusahaan yang akan ditampilkan di laporan.</li>
                                        <li><strong>Telepon Perusahaan</strong> - Nomor telepon perusahaan yang akan ditampilkan di laporan.</li>
                                        <li><strong>Email Perusahaan</strong> - Email perusahaan yang akan ditampilkan di laporan.</li>
                                        <li><strong>Jam Mulai Kerja</strong> - Jam mulai kerja normal.</li>
                                        <li><strong>Jam Selesai Kerja</strong> - Jam selesai kerja normal.</li>
                                        <li><strong>Izinkan Absensi di Akhir Pekan</strong> - Jika dicentang, karyawan dapat melakukan absensi di hari Sabtu dan Minggu.</li>
                                    </ul>
                                </div>
                                
                                <!-- Backup & Restore -->
                                <div class="tab-pane fade" id="v-pills-backup" role="tabpanel" aria-labelledby="v-pills-backup-tab">
                                    <h4>Panduan Backup & Restore</h4>
                                    <hr>
                                    
                                    <h5>Membuat Backup</h5>
                                    <p>Untuk membuat backup database, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Backup" pada sidebar.</li>
                                        <li>Klik tombol "Buat Backup Sekarang".</li>
                                        <li>File backup akan dibuat dan ditampilkan di daftar file backup.</li>
                                    </ol>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> Backup secara berkala untuk menghindari kehilangan data.
                                    </div>
                                    
                                    <h5>Mengunduh Backup</h5>
                                    <p>Untuk mengunduh file backup, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Backup" pada sidebar.</li>
                                        <li>Cari file backup yang ingin diunduh di daftar file backup.</li>
                                        <li>Klik tombol "Unduh" pada baris file backup tersebut.</li>
                                        <li>File backup akan diunduh ke komputer Anda.</li>
                                    </ol>
                                    
                                    <h5>Menghapus Backup</h5>
                                    <p>Untuk menghapus file backup, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Backup" pada sidebar.</li>
                                        <li>Cari file backup yang ingin dihapus di daftar file backup.</li>
                                        <li>Klik tombol "Hapus" pada baris file backup tersebut.</li>
                                        <li>Konfirmasi penghapusan file backup.</li>
                                    </ol>
                                    
                                    <h5>Merestore Database</h5>
                                    <p>Untuk merestore database dari file backup, ikuti langkah-langkah berikut:</p>
                                    <ol>
                                        <li>Klik menu "Backup" pada sidebar.</li>
                                        <li>Di bagian "Restore Database", klik tombol "Choose File".</li>
                                        <li>Pilih file backup yang ingin direstore.</li>
                                        <li>Klik tombol "Restore Database".</li>
                                        <li>Konfirmasi restore database.</li>
                                    </ol>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Restore database akan menimpa semua data yang ada. Pastikan Anda telah membuat backup terlebih dahulu.
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Profile -->
                            <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                <h4>Panduan Profil</h4>
                                <hr>
                                
                                <h5>Melihat Profil</h5>
                                <p>Untuk melihat profil Anda, ikuti langkah-langkah berikut:</p>
                                <ol>
                                    <li>Klik menu "Profil" pada sidebar.</li>
                                    <li>Anda akan melihat informasi profil Anda.</li>
                                </ol>
                                
                                <h5>Mengubah Profil</h5>
                                <p>Untuk mengubah profil Anda, ikuti langkah-langkah berikut:</p>
                                <ol>
                                    <li>Klik menu "Profil" pada sidebar.</li>
                                    <li>Ubah data profil sesuai kebutuhan.</li>
                                    <li>Klik tombol "Simpan Perubahan".</li>
                                </ol>
                                
                                <h5>Mengubah Password</h5>
                                <p>Untuk mengubah password Anda, ikuti langkah-langkah berikut:</p>
                                <ol>
                                    <li>Klik menu "Profil" pada sidebar.</li>
                                    <li>Isi bagian "Ubah Password" dengan password saat ini dan password baru.</li>
                                    <li>Klik tombol "Simpan Perubahan".</li>
                                </ol>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Password minimal 6 karakter.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
