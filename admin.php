<?php
require __DIR__ . '/config.php';
require __DIR__ . '/crypto.php';
require __DIR__ . '/layout.php';

if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

$sym  = $_SESSION['sym_key'];
$priv = $_SESSION['priv_key'];
$pub  = (string) db()->query('SELECT public_key FROM admin_keys ORDER BY id DESC LIMIT 1')->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $judul = trim($_POST['judul'] ?? '');
        $isi   = trim($_POST['isi'] ?? '');
        if ($judul === '' || $isi === '') {
            $_SESSION['flash'] = ['err', 'Judul dan isi wajib diisi.'];
        } else {
            $j   = aes_encrypt($judul, $sym);          // 1) enkripsi simetris AES-256-GCM
            $i   = aes_encrypt($isi, $sym);
            $sig = rsa_sign($j . '|' . $i, $priv);     // 2) tanda tangan digital RSA
            db()->prepare('INSERT INTO records (judul_enc, isi_enc, signature) VALUES (?,?,?)')->execute([$j, $i, $sig]);
            $_SESSION['flash'] = ['ok', 'Data berhasil dienkripsi dan disimpan.'];
        }
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM records WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        $_SESSION['flash'] = ['ok', 'Data dihapus.'];
    }
    header('Location: admin.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$rows = db()->query('SELECT * FROM records ORDER BY id DESC')->fetchAll();

layout_head('Halaman Admin');
?>
<div class="wrap">
  <div class="top">
    <div>
      <h1>🛡️ Halaman Admin</h1>
      <span class="muted">Total data: <?= count($rows) ?> · Enkripsi: AES-256-GCM · Tanda tangan: RSA-2048 + SHA-256</span>
    </div>
    <a class="btn" style="margin:0" href="logout.php">Logout</a>
  </div>

  <?php if ($flash): ?><div class="alert <?= $flash[0] === 'ok' ? 'ok' : 'err' ?>"><?= e($flash[1]) ?></div><?php endif; ?>

  <div class="card" style="margin-bottom:22px">
    <h2>➕ Simpan Data Baru (otomatis dienkripsi)</h2>
    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="add">
      <label for="judul">Judul</label>
      <input id="judul" name="judul" maxlength="150" required>
      <label for="isi">Isi / Konten</label>
      <textarea id="isi" name="isi" style="min-height:90px" required></textarea>
      <button type="submit">Enkripsi &amp; Simpan</button>
    </form>
  </div>

  <h2>📦 Seluruh Data Hasil Enkripsi</h2>
  <?php if (!$rows): ?><p class="muted">Belum ada data. Tambahkan data pada form di atas.</p><?php endif; ?>

  <?php foreach ($rows as $r):
      $valid  = rsa_verify($r['judul_enc'] . '|' . $r['isi_enc'], $r['signature'], $pub);
      $judulP = aes_decrypt($r['judul_enc'], $sym);
      $isiP   = aes_decrypt($r['isi_enc'], $sym);
  ?>
    <div class="card rec">
      <div class="head">
        <span><b>#<?= (int)$r['id'] ?></b> · <span class="muted"><?= e($r['created_at']) ?></span></span>
        <span>
          <span class="badge <?= $valid ? 'b-ok' : 'b-bad' ?>"><?= $valid ? '✔ Tanda tangan valid' : '✘ Tanda tangan tidak valid' ?></span>
          <form method="post" style="display:inline" onsubmit="return confirm('Hapus data ini?')">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="danger" type="submit">Hapus</button>
          </form>
        </span>
      </div>
      <div class="grid">
        <div class="box">
          <b>Ciphertext (tersimpan di database)</b>
          <div class="cipher"><u>Judul:</u> <?= e($r['judul_enc']) ?></div><br>
          <div class="cipher"><u>Isi:</u> <?= e($r['isi_enc']) ?></div><br>
          <div class="cipher"><u>Signature:</u> <?= e(substr($r['signature'], 0, 120)) ?>…</div>
        </div>
        <div class="box">
          <b>Plaintext (hasil dekripsi dengan kunci simetris)</b>
          <div class="plain"><u>Judul:</u> <?= $judulP === null ? '[GAGAL DEKRIPSI]' : e($judulP) ?></div><br>
          <div class="plain"><u>Isi:</u> <?= $isiP === null ? '[GAGAL DEKRIPSI]' : e($isiP) ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php layout_foot();
