<?php
require "koneksi.php";
require "cek_login.php";

$id = (int)($_POST['id'] ?? 0);

$stmt = $koneksi->prepare("SELECT status FROM pesanan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if ($data) {
    $posisi = array_search($data['status'], $urutan);

    if ($posisi !== false && isset($urutan[$posisi + 1])) {
        $baru = $urutan[$posisi + 1];

        $stmt = $koneksi->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $baru, $id);
        $stmt->execute();
    }
}

header("Location: pesanan.php");
exit;