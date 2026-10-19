<?php
require "koneksi.php";

// ---- 1. Cek status cucian (kalau pelanggan mengisi nomor nota) ----
$hasil = "";
$posisiStatus = -1;

if (isset($_GET['nota']) && trim($_GET['nota']) != "") {
    $nota = strtoupper(trim($_GET['nota']));

    $stmt = $koneksi->prepare(
        "SELECT pelanggan.nama, pesanan.status
         FROM pesanan
         JOIN pelanggan ON pelanggan.id = pesanan.id_pelanggan
         WHERE pesanan.nota = ?"
    );
    $stmt->bind_param("s", $nota);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data) {
        $posisiStatus = array_search($data['status'], $urutan);
        $hasil = "Nota " . e($nota) . " atas nama " . e($data['nama']) . ": " . e($data['status']) . ".";
    } else {
        $hasil = "Nota tidak ditemukan. Periksa nomor di struk kamu.";
    }
}
  


// ---- 2. Ambil daftar layanan dari database ----
$layanan = $koneksi->query("SELECT * FROM layanan ORDER BY id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Beranda - BilasBiru</title>
  <link rel="icon" href="assets/logo-ikon.svg" type="image/svg+xml">
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;700;800&family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

  <!-- ========== HEADER ========== -->
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
          <li><a href="riwayat.php">Riwayat Toko</a></li>
         
        </ul>
      </nav>

      <?php if (isset($_SESSION['user'])) { ?>
        <a href="logout.php" class="btn ghost">Keluar</a>
      <?php } else { ?>
        <a href="login.php" class="btn">Masuk</a>
      <?php } ?>
    </div>
  </header>

  <main>

    <!-- ========== HERO + CEK STATUS ========== -->
    <div class="hero">
      <div class="wrap">
        <div>
          <h1>Cucian bersih, statusnya bisa kamu pantau.</h1>
          <p>Masukkan nomor nota untuk melihat cucianmu sedang dicuci, disetrika, atau sudah siap diambil.</p>

          <form class="track" method="get" action="index.php">
            <input name="nota" placeholder="Contoh: LDR-001" aria-label="Nomor nota">
            <button class="btn" type="submit">Cek status</button>
          </form>

          <div id="hasil"><?= $hasil ?></div>
        </div>

        <!-- Gambar mesin cuci -->
        <svg class="machine" viewBox="0 0 400 400" role="img" aria-label="Mesin cuci">
          <rect x="60" y="40" width="280" height="330" rx="36" fill="#1E6BFF"/>
          <rect x="60" y="40" width="280" height="70" rx="36" fill="#3479ff"/>
          <circle cx="105" cy="75" r="10" fill="#fff"/>
          <circle cx="140" cy="75" r="10" fill="#3CC8E8"/>
          <circle cx="200" cy="215" r="98" fill="#DCEBFF"/>
          <circle cx="200" cy="215" r="80" fill="#0A2A6B"/>
          <g class="drum">
            <path d="M200 215 L200 140 A75 75 0 0 1 265 178Z" fill="#3CC8E8"/>
            <path d="M200 215 L200 290 A75 75 0 0 1 135 252Z" fill="#3479ff"/>
          </g>
          <circle cx="170" cy="200" r="8" fill="#fff" opacity=".8"/>
          <circle cx="225" cy="230" r="6" fill="#fff" opacity=".8"/>
        </svg>
      </div>
    </div>

    <!-- ========== DAFTAR LAYANAN (dari database) ========== -->
    <section id="layanan">
      <div class="wrap">
        <h2>Layanan laundry</h2>
        <p class="sub">Harga dihitung per kilogram.</p>

        <div class="services">
          <?php while ($l = $layanan->fetch_assoc()) { ?>
            <div class="svc">
              <h3><?= e($l['nama']) ?></h3>
              <div class="price"><?= rupiah($l['harga_per_kg']) ?> <small>/kg</small></div>
              <p><?= e($l['keterangan']) ?></p>
            </div>
          <?php } ?>
        </div>
      </div>
    </section>

    <!-- ========== ALUR CUCIAN ========== -->
    <section id="alur" class="flow">
      <div class="wrap">
        <h2>Alur cucianmu</h2>
        <p class="sub">Setiap nota melewati lima tahap ini.</p>

       <?php
$keterangan = ['Ditimbang dan dicatat', 'Mesin dan deterjen', 'Dilipat rapi', 'Kabar dikirim','Dibawa pulang'];
?>
<ol class="steps">
  <?php foreach ($urutan as $i => $namaTahap) {
      $kelas = "";
      if ($posisiStatus >= 0) {
          if ($i < $posisiStatus) {
              $kelas = "done";
          } elseif ($i == $posisiStatus) {
              $kelas = "now";
          }
      }
  ?>
    <li class="<?= $kelas ?>">
      <?= e($namaTahap) ?><small><?= $keterangan[$i] ?></small>
    </li>
  <?php } ?>
</ol>
      </div>
    </section>

  </main>

  <!-- ========== FOOTER ========== -->
  <footer>
    <div class="wrap">BilasBiru &copy; <?= date("Y") ?>. Sistem informasi laundry.</div>
  </footer>

</body>
</html>