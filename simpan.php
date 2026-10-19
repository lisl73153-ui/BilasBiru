<?php
require "koneksi.php";
require "cek_login.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: pesanan.php");
    exit;
}

// ---- Ambil isian form ----
$nama       = trim($_POST['nama'] ?? "");
$no_hp      = trim($_POST['no_hp'] ?? "");
$id_layanan = (int)($_POST['id_layanan'] ?? 0);
$berat      = (float)($_POST['berat'] ?? 0);
$metode     = $_POST['metode'] ?? "Belum";

if ($nama == "" || $id_layanan <= 0 || $berat <= 0) {
    header("Location: pesanan.php?pesan=gagal");
    exit;
}

// ---- 1. Ambil harga dan nama layanan dari database ----
$stmt = $koneksi->prepare("SELECT nama, harga_per_kg FROM layanan WHERE id = ?");
$stmt->bind_param("i", $id_layanan);
$stmt->execute();
$layanan = $stmt->get_result()->fetch_assoc();

if (!$layanan) {
    header("Location: pesanan.php?pesan=gagal");
    exit;
}

$nama_layanan = $layanan['nama'];
$harga        = $layanan['harga_per_kg'];
$subtotal     = (int)round($harga * $berat);

// ---- 2. Pelanggan: pakai yang sudah ada, kalau belum ada buat baru ----
$stmt = $koneksi->prepare("SELECT id FROM pelanggan WHERE nama = ? AND no_hp = ?");
$stmt->bind_param("ss", $nama, $no_hp);
$stmt->execute();
$pelanggan = $stmt->get_result()->fetch_assoc();

if ($pelanggan) {
    $id_pelanggan = $pelanggan['id'];
} else {
    $stmt = $koneksi->prepare("INSERT INTO pelanggan (nama, no_hp) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama, $no_hp);
    $stmt->execute();
    $id_pelanggan = $stmt->insert_id;
}

// ---- 3. Simpan pesanan (satu nota) beserta tanggal masuk ----
$id_pengguna   = $_SESSION['user']['id'];
$tanggal_masuk = date('Y-m-d H:i:s'); // Mengambil tanggal & jam saat ini

$stmt = $koneksi->prepare(
    "INSERT INTO pesanan (id_pelanggan, id_pengguna, total, tanggal_masuk) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("iiis", $id_pelanggan, $id_pengguna, $subtotal, $tanggal_masuk);
$stmt->execute();
$id_pesanan = $koneksi->insert_id;

// Nomor nota otomatis, contoh: LDR-001
$nota = "LDR-" . str_pad($id_pesanan, 3, "0", STR_PAD_LEFT);
$stmt = $koneksi->prepare("UPDATE pesanan SET nota = ? WHERE id = ?");
$stmt->bind_param("si", $nota, $id_pesanan);
$stmt->execute();

// ---- 4. Simpan rincian isi nota ----
$stmt = $koneksi->prepare(
    "INSERT INTO detail_pesanan (id_pesanan, id_layanan, berat, harga_satuan, subtotal)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("iidii", $id_pesanan, $id_layanan, $berat, $harga, $subtotal);
$stmt->execute();

// ---- 5. Catat pembayaran kalau langsung dibayar ----
if (in_array($metode, ['Tunai', 'Transfer', 'QRIS'])) {
    $stmt = $koneksi->prepare(
        "INSERT INTO pembayaran (id_pesanan, jumlah, metode) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iis", $id_pesanan, $subtotal, $metode);
    $stmt->execute();
}

// ---- 6. Catat otomatis ke riwayat toko (permanen untuk rekap data toko) ----
$stmt_rekap = $koneksi->prepare(
    "INSERT INTO riwayat_toko (nota, nama_pelanggan, nama_layanan, berat, total, tanggal_masuk, status_terakhir) 
     VALUES (?, ?, ?, ?, ?, ?, 'Diterima')"
);
$stmt_rekap->bind_param("sssdis", $nota, $nama, $nama_layanan, $berat, $subtotal, $tanggal_masuk);
$stmt_rekap->execute();

header("Location: pesanan.php?pesan=ok");
exit;