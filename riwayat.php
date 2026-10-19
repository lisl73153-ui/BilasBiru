<?php
require "koneksi.php";
require "cek_login.php";

// Ambil semua data riwayat toko dari database
$riwayat = $koneksi->query("SELECT * FROM riwayat_toko ORDER BY id DESC");

// Hitung total ringkasan rekap toko
$total_transaksi = $koneksi->query("SELECT COUNT(*) AS jml, SUM(total) AS omzet, SUM(berat) AS total_berat FROM riwayat_toko")->fetch_assoc();

// Cari layanan yang paling laris
$layanan_terlaris = $koneksi->query("SELECT nama_layanan, COUNT(*) AS jumlah_pesanan FROM riwayat_toko GROUP BY nama_layanan ORDER BY jumlah_pesanan DESC LIMIT 1")->fetch_assoc();
$nama_terlaris = $layanan_terlaris ? $layanan_terlaris['nama_layanan'] . " (" . $layanan_terlaris['jumlah_pesanan'] . "x pesanan)" : "Belum ada data";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Riwayat Toko - BilasBiru</title>
  <link rel="icon" href="assets/logo-ikon.svg" type="image/svg+xml">
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;700;800&family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

  <header>
    <div class="wrap nav">
      <a href="index.php" class="logo">
        <img src="assets/logo-ikon.svg" alt="" width="44" height="44">
        <span class="logo-text">Bilas<span>Biru</span></span>
      </a>
      <nav>
        <ul>
          <li><a href="index.php#layanan">Layanan</a></li>
          <li><a href="index.php#alur">Alur cucian</a></li>
          <?php if (isset($_SESSION['user'])) { ?>
            <li><a href="pesanan.php">Pesanan</a></li>
          <?php } ?>
          <li><a href="riwayat.php" style="font-weight: bold; color: #0284c7;">Riwayat Toko</a></li>
         
        </ul>
      </nav>
      <a href="logout.php" class="btn ghost">Keluar</a>
    </div>
  </header>

  <main style="padding-top: 30px;">
    <section>
      <div class="wrap">

        <div style="margin-bottom: 24px;">
          <h2 style="font-size: 24px; margin-bottom: 4px;">Riwayat & Rekap Toko</h2>
          <p class="sub" style="margin: 0;">Semua catatan pesanan yang pernah masuk ke tokomu tersimpan permanen di sini.</p>
        </div>

        <!-- Kartu Ringkasan Statistik -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div class="card" style="padding: 16px; text-align: center;">
            <small style="color: #666;">Total Pesanan Masuk</small>
            <h3 style="font-size: 24px; margin-top: 4px; color: #0284c7;"><?= (int)$total_transaksi['jml'] ?> Nota</h3>
          </div>
          <div class="card" style="padding: 16px; text-align: center;">
            <small style="color: #666;">Total Berat Cucian</small>
            <h3 style="font-size: 24px; margin-top: 4px; color: #0284c7;"><?= number_format($total_transaksi['total_berat'], 1, ',', '.') ?> kg</h3>
          </div>
          <div class="card" style="padding: 16px; text-align: center;">
            <small style="color: #666;">Estimasi Total Pendapatan</small>
            <h3 style="font-size: 24px; margin-top: 4px; color: #16a34a;"><?= rupiah($total_transaksi['omzet']) ?></h3>
          </div>
          <div class="card" style="padding: 16px; text-align: center;">
            <small style="color: #666;">Layanan Paling Laris</small>
            <h3 style="font-size: 18px; margin-top: 4px; color: #d97706;"><?= e($nama_terlaris) ?></h3>
          </div>
        </div>

        <!-- Tabel Daftar Riwayat -->
        <div class="card">
          <h2 style="font-size: 20px; margin-bottom: 16px;">Semua Riwayat Pesanan</h2>
          <div class="tbl">
            <table>
              <thead>
                <tr>
                  <th>Nota & Waktu</th>
                  <th>Pelanggan</th>
                  <th>Layanan & Berat</th>
                  <th>Total Harga</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($r = $riwayat->fetch_assoc()) { ?>
                  <tr>
                    <td>
                      <strong><?= e($r['nota']) ?></strong><br>
                      <small style="color: #555;">Masuk: <?= date('d/m/Y H:i', strtotime($r['tanggal_masuk'])) ?></small>
                    </td>
                    <td>
                      <strong><?= e($r['nama_pelanggan']) ?></strong>
                    </td>
                    <td>
                      <?= e($r['nama_layanan']) ?><br>
                      <small style="color: #666;"><?= $r['berat'] ?> kg</small>
                    </td>
                    <td><?= rupiah($r['total']) ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </section>
  </main>

  <footer>
    <div class="wrap">BilasBiru &copy; <?= date("Y") ?>. Sistem informasi laundry.</div>
  </footer>

</body>
</html>