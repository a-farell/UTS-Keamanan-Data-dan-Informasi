<?php
require __DIR__ . '/config.php';
require __DIR__ . '/crypto.php';
require __DIR__ . '/layout.php';

if (is_logged_in()) {
    header('Location: admin.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $sym  = trim($_POST['sym_key'] ?? '');
    $priv = trim($_POST['priv_key'] ?? '');
    $row  = false;
    try {
        $row = db()->query('SELECT * FROM admin_keys ORDER BY id DESC LIMIT 1')->fetch();
    } catch (Throwable $t) {
        $error = 'Database belum siap. Pastikan database.sql sudah di-import dan config.php benar.';
    }

    if ($error === '') {
        if (!$row) {
            $error = 'Kunci belum dibuat. Buka setup.php terlebih dahulu.';
        } elseif ($sym === '' || $priv === '') {
            $error = 'Kunci simetris dan kunci privat asimetris wajib diisi.';
        } else {
            $keysValid = password_verify($sym, $row['sym_hash'])
                && verify_private_key($priv, $row['public_key']);
            session_regenerate_id(true);
            $_SESSION['sym_key']  = $sym;
            $_SESSION['priv_key'] = $priv;
            $_SESSION['keys_valid'] = $keysValid;
            header('Location: admin.php');
            exit;
        }
    }
}

layout_head('Sistem Informasi Data Terenkripsi');
?>
<main class="login-page">
  <div class="login-shell">
    <section class="login-intro">
      <a class="brand" href="index.php"><span class="brand-mark">S</span> SecureInfo</a>
      <div class="eyebrow"><span class="status-dot"></span> PRIVATE DATA VAULT</div>
      <h1>Data penting.<br><span>Perlindungan berlapis.</span></h1>
      <p class="intro-copy">Simpan dan kelola informasi dalam ruang digital yang dirancang untuk menjaga privasi.</p>
      <div class="feature-list">
        <div class="feature-item"><span class="feature-icon">01</span><span><b>Enkripsi AES-256</b><small>Data terlindungi saat disimpan</small></span></div>
        <div class="feature-item"><span class="feature-icon">02</span><span><b>Verifikasi RSA-2048</b><small>Akses menggunakan pasangan kunci</small></span></div>
      </div>
      <div class="intro-foot"><span class="intro-line"></span> SECURE INFORMATION SYSTEM</div>
    </section>

    <section class="card login-card">
      <div class="card-heading">
        <div class="card-icon">↗</div>
        <div class="eyebrow">SELAMAT DATANG KEMBALI</div>
        <h2>Masuk ke akun</h2>
        <p class="muted">Gunakan kunci enkripsi Anda untuk melanjutkan.</p>
      </div>

      <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

      <form method="post" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <label for="sym_key">Kunci simetris <span>AES-256</span></label>
        <input type="password" id="sym_key" name="sym_key" placeholder="Tempel kunci simetris" autocomplete="off" required>

        <label for="priv_key">Kunci privat <span>RSA-2048 · PEM</span></label>
        <textarea id="priv_key" name="priv_key" placeholder="-----BEGIN PRIVATE KEY-----&#10;..." autocomplete="off" spellcheck="false" required></textarea>

        <button type="submit">Masuk ke dashboard <span aria-hidden="true">→</span></button>
      </form>
      <div class="login-footer">
        <span>Belum memiliki kunci?</span>
        <a href="setup.php">Setup kunci awal <span aria-hidden="true">↗</span></a>
      </div>
      <div class="secure-caption"><span aria-hidden="true">◆</span> Koneksi sesi dilindungi token keamanan</div>
    </section>
  </div>
</main>
<?php layout_foot();
