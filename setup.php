<?php
require __DIR__ . '/config.php';
require __DIR__ . '/crypto.php';
require __DIR__ . '/layout.php';

$error = '';
$exists = false;
$generated = null;

try {
    $exists = (bool) db()->query('SELECT 1 FROM admin_keys LIMIT 1')->fetchColumn();
} catch (Throwable $t) {
    $error = 'Tidak bisa terhubung ke database / tabel belum ada. Import database.sql dan cek config.php.';
}

if ($error === '' && !$exists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $sym = bin2hex(random_bytes(32));          // kunci simetris 256-bit (64 hex)
        [$priv, $pub] = rsa_generate();            // pasangan kunci RSA-2048
        $stmt = db()->prepare('INSERT INTO admin_keys (sym_hash, public_key) VALUES (?, ?)');
        $stmt->execute([password_hash($sym, PASSWORD_DEFAULT), $pub]);
        $generated = ['sym' => $sym, 'priv' => $priv];
        $exists = true;
    } catch (Throwable $t) {
        $error = 'Gagal membuat kunci: ' . $t->getMessage();
    }
}

layout_head('Setup Kunci');
?>
<div class="wrap">
  <div class="card">
    <h1>⚙️ Setup Kunci Admin</h1>
    <p class="muted">Halaman ini membuat kunci simetris & pasangan kunci asimetris. Server hanya menyimpan
       <b>hash</b> kunci simetris dan <b>public key</b>. Kunci rahasia hanya ditampilkan SEKALI.</p>

    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

    <?php if ($generated): ?>
      <div class="alert warn">⚠️ Simpan kedua kunci ini sekarang! Tidak akan ditampilkan lagi.</div>

      <label>Kunci Simetris (AES-256)</label>
      <textarea readonly style="min-height:60px"><?= e($generated['sym']) ?></textarea>
      <a class="btn" download="kunci_simetris.txt"
         href="data:text/plain;base64,<?= base64_encode($generated['sym']) ?>">⬇ Download kunci simetris</a>

      <label>Kunci Privat Asimetris (RSA-2048)</label>
      <textarea readonly style="min-height:220px"><?= e($generated['priv']) ?></textarea>
      <a class="btn" download="kunci_privat.pem"
         href="data:text/plain;base64,<?= base64_encode($generated['priv']) ?>">⬇ Download kunci privat</a>

      <p><a class="btn" href="index.php">Lanjut ke Halaman Login →</a></p>

    <?php elseif ($exists): ?>
      <div class="alert ok">Kunci sudah pernah dibuat. Silakan login di <a href="index.php">halaman utama</a>.</div>
      <p class="muted">Untuk membuat ulang kunci, jalankan di phpMyAdmin:
         <code>TRUNCATE admin_keys; TRUNCATE records;</code> (data lama tidak bisa didekripsi lagi dengan kunci baru).</p>

    <?php elseif ($error === ''): ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <button type="submit">Generate Kunci Sekarang</button>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php layout_foot();
