<?php
require "koneksi.php";

// Kalau sudah login, langsung ke halaman pesanan
if (isset($_SESSION['user'])) {
    header("Location: pesanan.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? "");
    $password = $_POST['password'] ?? "";

    $stmt = $koneksi->prepare(
        "SELECT id, password_hash, nama_lengkap, role FROM pengguna WHERE username = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();

    if ($u && password_verify($password, $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'   => $u['id'],
            'nama' => $u['nama_lengkap'],
            'role' => $u['role'],
        ];
        header("Location: pesanan.php");
        exit;
    }

    $error = "Username atau password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk - BilasBiru</title>
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
        </ul>
      </nav>
      <a href="index.php" class="btn ghost">Beranda</a>
    </div>
  </header>

  <main>
    <section>
      <div class="wrap" style="max-width: 420px;">
        <div class="card">
          <div style="text-align: center; margin-bottom: 16px;">
            <img src="assets/logo-ikon.svg" alt="" width="56" height="56">
            <h2>Masuk</h2>
            <p class="sub" style="margin: 0 auto;">Khusus petugas BilasBiru</p>
          </div>

          <?php if ($error != "") { ?>
            <div class="notif" role="alert"><?= e($error) ?></div>
          <?php } ?>

          <form method="post" action="login.php">
            <label>Username
              <input name="username" placeholder="Masukkan username" required autofocus>
            </label>
            <label>Password
              <input name="password" type="password" placeholder="Masukkan password" required>
            </label>
            <button class="btn" type="submit">Masuk</button>
          </form>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <div class="wrap">BilasBiru &copy; <?= date("Y") ?>. Sistem informasi laundry.</div>
  </footer>

</body>
</html>