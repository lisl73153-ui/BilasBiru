<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$koneksi = new mysqli("localhost", "root", "", "bilasbiru");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
$koneksi->set_charset("utf8mb4");

$urutan = ['Diterima', 'Dicuci', 'Disetrika', 'Siap diambil', 'Sudah diambil'];

$kelasStatus = [
    'Diterima'      => 't-baru',
    'Dicuci'        => 't-cuci',
    'Disetrika'     => 't-setrika',
    'Siap diambil'  => 't-siap',
    'Sudah diambil' => 't-selesai',
];

function e($teks) {
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}

function rupiah($angka) {
    return "Rp " . number_format($angka, 0, ",", ".");
}
?>