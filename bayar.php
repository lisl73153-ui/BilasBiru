<?php
require "koneksi.php";
require "cek_login.php";

$id = (int)($_POST['id'] ?? 0);

$stmt = $koneksi->prepare(
    "SELECT total,
            (SELECT COALESCE(SUM(jumlah), 0)
               FROM pembayaran
              WHERE pembayaran.id_pesanan = pesanan.id) AS dibayar
       FROM pesanan
      WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if ($data) {
    $sisa = $data['total'] - $data['dibayar'];

    if ($sisa > 0) {
        $stmt = $koneksi->prepare(
            "INSERT INTO pembayaran (id_pesanan, jumlah, metode) VALUES (?, ?, 'Tunai')"
        );
        $stmt->bind_param("ii", $id, $sisa);
        $stmt->execute();
    }
}

header("Location: pesanan.php?pesan=lunas");
exit;