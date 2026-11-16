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

    if (($_SESSION['lock_until'] ?? 0) > time()) {
        $error = 'Terlalu banyak percobaan gagal. Coba lagi dalam ' . ($_SESSION['lock_until'] - time()) . ' detik.';
    } else {
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
            } elseif (password_verify($sym, $row['sym_hash']) && verify_private_key($priv, $row['public_key'])) {
                session_regenerate_id(true);
                $_SESSION['sym_key']  = $sym;
                $_SESSION['priv_key'] = $priv;
                $_SESSION['fail'] = 0;
                unset($_SESSION['lock_until']);
                header('Location: admin.php');
                exit;
            } else {
                $_SESSION['fail'] = ($_SESSION['fail'] ?? 0) + 1;
                if ($_SESSION['fail'] >= 5) {
                    $_SESSION['lock_until'] = time() + 60;
                    $_SESSION['fail'] = 0;
                }
                $error = 'Kunci tidak valid. Akses ditolak.';
            }
        }
    }
}

layout_head('Sistem Informasi Data Terenkripsi');
?>
<div class="center">
  <div class="card narrow">
    <h1>🔐 SecureInfo</h1>
    <p class="muted">Sistem Informasi Data Terenkripsi. Akses halaman admin menggunakan
       <b>kunci simetris</b> (AES-256) dan <b>kunci privat asimetris</b> (RSA-2048).</p>

    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <label for="sym_key">Kunci Simetris (AES-256, 64 karakter hex)</label>
      <input type="password" id="sym_key" name="sym_key" placeholder="Tempel kunci simetris di sini" required>

      <label for="priv_key">Kunci Privat Asimetris (RSA, format PEM)</label>
      <textarea id="priv_key" name="priv_key" placeholder="-----BEGIN PRIVATE KEY-----&#10;...&#10;-----END PRIVATE KEY-----" required></textarea>

      <button type="submit">Masuk ke Halaman Admin</button>
    </form>
    <p class="muted" style="margin-top:18px">Belum punya kunci? <a href="setup.php">Generate kunci (setup awal)</a></p>
  </div>
</div>
<?php layout_foot();
