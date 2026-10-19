<?php
require "koneksi.php";
require "cek_login.php";

$pesan = $_GET['pesan'] ?? "";

// Daftar layanan untuk pilihan di form
$layanan = $koneksi->query("SELECT * FROM layanan ORDER BY id");

// Daftar pesanan
$pesanan = $koneksi->query(
    "SELECT pesanan.id, pesanan.nota, pesanan.total, pesanan.status, pesanan.tanggal_masuk,
            pelanggan.nama AS nama_pelanggan,
            (SELECT GROUP_CONCAT(layanan.nama SEPARATOR ', ')
               FROM detail_pesanan
               JOIN layanan ON layanan.id = detail_pesanan.id_layanan
              WHERE detail_pesanan.id_pesanan = pesanan.id) AS nama_layanan,
            (SELECT COALESCE(SUM(jumlah), 0)
               FROM pembayaran
              WHERE pembayaran.id_pesanan = pesanan.id) AS dibayar
       FROM pesanan
       JOIN pelanggan ON pelanggan.id = pesanan.id_pelanggan
     ORDER BY pesanan.id DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pesanan - BilasBiru</title>
  <link rel="icon" href="assets/logo-ikon.svg" type="image/svg+xml">
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;700;800&family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
  <style>
    .orders {
      display: grid;
      grid-template-columns: 380px 1fr;
      gap: 24px;
      align-items: start;
    }
    @media (max-width: 950px) {
      .orders { grid-template-columns: 1fr; }
    }
    .tbl table { width: 100%; border-collapse: collapse; }
    .tbl th, .tbl td { padding: 14px 10px; vertical-align: middle; text-align: left; }
    /* Memperbesar ukuran teks nama dan jenis layanan */
    .nama-pelanggan { font-size: 15px; font-weight: bold; color: #111; }
    .jenis-layanan { font-size: 13px; font-weight: 600; color: #0284c7; margin-top: 2px; }
    .info-waktu { font-size: 12px; color: #555; line-height: 1.4; }
    .info-ambil { font-size: 12px; color: #0284c7; font-weight: 600; }
  </style>
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
            <li><a href="pesanan.php" style="font-weight: bold; color: #0284c7;">Pesanan</a></li>
          <?php } ?>
          <li><a href="riwayat.php">Riwayat Toko</a></li>
          
        </ul>
      </nav>
      <a href="logout.php" class="btn ghost">Keluar</a>
    </div>
  </header>

  <main style="padding-top: 30px;">
    <section>
      <div class="wrap">

        <div style="margin-bottom: 24px;">
          <h2 style="font-size: 24px; margin-bottom: 4px;">Halo, <?= e($_SESSION['user']['nama']) ?></h2>
          <p class="sub" style="margin: 0;">Catat cucian baru dan pantau statusnya di sini.</p>
        </div>

        <?php if ($pesan == "ok") { ?>
          <div class="notif" role="status">Pesanan tersimpan.</div>
        <?php } elseif ($pesan == "lunas") { ?>
          <div class="notif" role="status">Pembayaran tercatat.</div>
        <?php } elseif ($pesan == "gagal") { ?>
          <div class="notif" role="alert">Data belum lengkap. Isi nama, layanan, dan berat.</div>
        <?php } ?>

        <div class="orders">

          <!-- ===== FORM PESANAN BARU (Kiri) ===== -->
          <div class="card">
            <h2 style="font-size: 20px; margin-bottom: 16px;">Pesanan baru</h2>
            <form method="post" action="simpan.php">
              <label>Nama pelanggan
                <input name="nama" placeholder="Nama lengkap" required>
              </label>
              <label>No. HP
                <input name="no_hp" placeholder="08xxxxxxxxxx">
              </label>
              <label>Layanan
                <select name="id_layanan">
                  <?php while ($l = $layanan->fetch_assoc()) { ?>
                    <option value="<?= (int)$l['id'] ?>">
                      <?= e($l['nama']) ?> (<?= rupiah($l['harga_per_kg']) ?>/kg)
                    </option>
                  <?php } ?>
                </select>
              </label>
              <label>Berat (kg)
                <input name="berat" type="number" min="0.5" step="0.5" placeholder="3" required>
              </label>
              <label>Pembayaran
                <select name="metode">
                  <option value="Belum">Belum bayar</option>
                  <option value="Tunai">Tunai</option>
                  <option value="Transfer">Transfer</option>
                  <option value="QRIS">QRIS</option>
                </select>
              </label>
              <button class="btn" type="submit" style="width: 100%; margin-top: 6px;">Simpan pesanan</button>
            </form>
          </div>

          <!-- ===== DAFTAR PESANAN (Kanan) ===== -->
          <div class="card">
            <h2 style="font-size: 20px; margin-bottom: 16px;">Daftar pesanan</h2>
            <div class="tbl">
              <table>
                <thead>
                  <tr>
                    <th>Nota</th>
                    <th>Pelanggan</th>
                    <th>Waktu Masuk / Ambil</th>
                    <th>Total</th>
                    <th>Bayar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($p = $pesanan->fetch_assoc()) { 
                    $tgl_masuk = $p['tanggal_masuk'] ? $p['tanggal_masuk'] : date('Y-m-d H:i:s');
                    
                    if (stripos($p['nama_layanan'], 'kilat') !== false || stripos($p['nama_layanan'], '6 jam') !== false) {
                        $tgl_estimasi = date('d/m/Y H:i', strtotime($tgl_masuk . ' + 6 hours'));
                    } else {
                        $tgl_estimasi = date('d/m/Y', strtotime($tgl_masuk . ' + 2 days'));
                    }
                  ?>
                    <tr class="<?= $p['status'] == 'Sudah diambil' ? 'selesai' : '' ?>">
                      <td><strong><?= e($p['nota']) ?></strong></td>
                      <td>
                        <div class="nama-pelanggan"><?= e($p['nama_pelanggan']) ?></div>
                        <div class="jenis-layanan"><?= e($p['nama_layanan']) ?></div>
                      </td>
                      <td>
                        <div class="info-waktu">Masuk: <?= date('d/m/Y H:i', strtotime($tgl_masuk)) ?></div>
                        <div class="info-ambil">Ambil: <?= $tgl_estimasi ?></div>
                      </td>
                      <td><?= rupiah($p['total']) ?></td>
                      <td>
                        <?php if ($p['dibayar'] >= $p['total']) { ?>
                          <span class="tag t-siap">Lunas</span>
                        <?php } else { ?>
                          <span class="tag t-baru">Belum</span>
                        <?php } ?>
                      </td>
                      <td>
                        <span class="tag <?= $kelasStatus[$p['status']] ?? 't-baru' ?>"><?= e($p['status']) ?></span>
                      </td>
                      <td>
                        <?php if ($p['status'] != 'Sudah diambil') { ?>
                          <form class="satu" method="post" action="ubah_status.php">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button class="btn mini" type="submit">
                              <?= $p['status'] == 'Siap diambil' ? 'Selesai' : 'Lanjut' ?>
                            </button>
                          </form>
                        <?php } ?>

                        <?php if ($p['dibayar'] < $p['total']) { ?>
                          <form class="satu" method="post" action="bayar.php" style="margin-top: 4px;">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button class="btn mini ghost" type="submit">Lunasi</button>
                          </form>
                        <?php } ?>

                        <!-- Tombol Hapus -->
                        <a href="hapus.php?id=<?= (int)$p['id'] ?>" class="btn mini ghost" style="margin-top: 4px; color: #dc2626; border-color: #dc2626; display: inline-block; text-align: center; text-decoration: none;" onclick="return confirm('Yakin ingin menghapus pesanan ini?')">Hapus</a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
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