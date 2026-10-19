<?php
require "koneksi.php";
require "cek_login.php";

// Pastikan parameter id benar-benar ada dan berupa angka
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Hapus data terkait pembayaran & detail pesanan dulu
    $koneksi->query("DELETE FROM pembayaran WHERE id_pesanan = $id");
    $koneksi->query("DELETE FROM detail_pesanan WHERE id_pesanan = $id");

    // Hapus pesanan utamanya
    $koneksi->query("DELETE FROM pesanan WHERE id = $id");
}

// Langsung kembalikan ke halaman pesanan
header("Location: pesanan.php");
exit;
?>