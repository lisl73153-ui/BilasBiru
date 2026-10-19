# BilasBiru
Aplikasi Sistem Informasi Management Laundry(Laundry App). Aplikasi Web Manajemen Laundry - BilasBiru Aplikasi web sistem informasi laundry BilasBiru yang dirancang khusus untuk mempermudah pengelolaan transaksi, pemantauan status cucian pelanggan secara real-time, serta rekapitulasi laporan toko secara otomatis. Fitur Utama Aplikasi:  * Cek Status Cucian Tanpa Login (Halaman Publik):    * Pelanggan dapat langsung melacak status cucian mereka (apakah sedang Diterima, Dicuci, Disetrika, Siap diambil, atau Sudah diambil) cukup dengan memasukkan nomor nota (misal: LDR-001) di halaman utama.  * Informasi Layanan & Alur Cucian:    * Menampilkan daftar pilihan paket layanan lengkap (seperti Cuci kering, Cuci setrika, hingga layanan Kilat 6 jam) beserta estimasi harga per kilogram dan durasi pengerjaannya.    * Dilengkapi panduan alur tahapan proses pengerjaan cucian bagi pelanggan.  * Login Petugas (Admin / Kasir):    * Sistem login khusus pengelola/petugas toko untuk mengakses menu manajemen operasional laundry.  * Input Pesanan & Manajemen Transaksi:    * Petugas dapat menginput data pelanggan baru, nomor HP, memilih jenis layanan, memasukkan berat cucian (kg), serta mengatur status pembayaran (Belum bayar / Lunas) dan status pengerjaan cucian.  * Riwayat & Rekap Toko (Dashboard Laporan):    * Halaman rekapitulasi otomatis yang menampilkan total nota pesanan masuk, total berat cucian keseluruhan (kg), estimasi total pendapatan toko, hingga statistik jenis layanan laundry yang paling laris 
Aplikasi ini menggunakan database MySQL bernama bilasbiru dengan tabel-tabel berikut:
detail_pesanan – Menyimpan rincian item atau item tambahan dalam pesanan.  
layanan – Menyimpan daftar paket layanan laundry dan harganya.  
membership – Menyimpan data keanggotaan/member pelanggan.  
pelanggan – Menyimpan data identitas pelanggan.  
pembayaran – Menyimpan transaksi dan status pembayaran.  
pengguna – Menyimpan data akun petugas/admin untuk login.  
pesanan – Menyimpan data utama pesanan cucian dan status pengerjaannya.  
riwayat_toko – Menyimpan rekapitulasi dan riwayat operasional toko.


