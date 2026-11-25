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
$keysValid = !empty($_SESSION['keys_valid']);
$pub  = (string) db()->query('SELECT public_key FROM admin_keys ORDER BY id DESC LIMIT 1')->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($keysValid) {
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
    }
    header('Location: admin.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$rows = db()->query('SELECT * FROM records ORDER BY id DESC')->fetchAll();
$displayText = static function (string $text) use ($keysValid, $sym): string {
    return '<span class="ciphertext-text">' . e($keysValid ? $text : aes_encrypt($text, $sym)) . '</span>';
};

layout_head($keysValid ? 'Halaman Admin' : aes_encrypt('Halaman Admin', $sym));
?>
<div class="wrap">
  <div class="top">
    <div>
      <h1>🛡️ <?= $displayText('Halaman Admin') ?></h1>
      <span class="muted"><?= $displayText('Total data: ' . count($rows) . ' · Enkripsi: AES-256-GCM · Tanda tangan: RSA-2048 + SHA-256') ?></span>
    </div>
    <a class="btn" style="margin:0" href="logout.php"><?= $displayText('Logout') ?></a>
  </div>

  <?php if ($flash): ?><div class="alert <?= e($flash[0]) ?>"><?= $displayText((string)$flash[1]) ?></div><?php endif; ?>

  <?php if ($keysValid): ?>
  <div class="card form-card">
    <div class="section-heading">
      <div><div class="eyebrow">VAULT ACTION</div><h2>Simpan data baru</h2></div>
      <span class="badge b-ok">AES-256-GCM</span>
    </div>
    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="add">
      <label for="judul">Judul data</label>
      <input id="judul" name="judul" maxlength="150" required>
      <label for="isi">Isi atau konten</label>
      <textarea id="isi" name="isi" class="content-input" required></textarea>
      <button type="submit">Enkripsi &amp; simpan <span aria-hidden="true">→</span></button>
    </form>
  </div>
  <?php endif; ?>

  <div class="section-heading records-heading">
    <div><div class="eyebrow"><?= $displayText('ENCRYPTED STORAGE') ?></div><h2><?= $displayText('Data tersimpan') ?></h2></div>
    <span class="record-count"><?= $displayText(count($rows) . ' item') ?></span>
  </div>
  <?php if (!$rows): ?><div class="empty-state"><span class="empty-icon">◇</span><b><?= $displayText('Vault masih kosong') ?></b><p class="muted"><?= $displayText('Data yang disimpan akan muncul di sini dalam format terenkripsi.') ?></p></div><?php endif; ?>

  <?php foreach ($rows as $r):
      $valid  = rsa_verify($r['judul_enc'] . '|' . $r['isi_enc'], $r['signature'], $pub);
      $judulP = $keysValid ? aes_decrypt($r['judul_enc'], $sym) : null;
      $isiP   = $keysValid ? aes_decrypt($r['isi_enc'], $sym) : null;
  ?>
    <div class="card rec">
      <div class="head">
        <span><b><?= $displayText('#' . (int)$r['id']) ?></b> · <span class="muted"><?= $displayText($r['created_at']) ?></span></span>
        <span>
          <span class="badge <?= $valid ? 'b-ok' : 'b-bad' ?>"><?= $displayText($valid ? 'Tanda tangan valid' : 'Tanda tangan tidak valid') ?></span>
          <?php if ($keysValid): ?><form method="post" style="display:inline" onsubmit="return confirm('Hapus data ini?')">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="danger" type="submit">Hapus</button>
          </form><?php endif; ?>
        </span>
      </div>
      <div class="grid">
        <div class="box">
          <b><?= $displayText('Ciphertext (tersimpan di database)') ?></b>
          <div class="cipher"><u><?= $displayText('Judul:') ?></u> <?= $displayText($r['judul_enc']) ?></div><br>
          <div class="cipher"><u><?= $displayText('Isi:') ?></u> <?= $displayText($r['isi_enc']) ?></div><br>
          <div class="cipher"><u><?= $displayText('Signature:') ?></u> <?= $displayText(substr($r['signature'], 0, 120) . '…') ?></div>
        </div>
        <div class="box">
          <b><?= $displayText($keysValid ? 'Plaintext (hasil dekripsi dengan kunci simetris)' : 'Plaintext (terkunci)') ?></b>
          <?php if ($keysValid): ?>
          <div class="plain"><u><?= $displayText('Judul:') ?></u> <?= $judulP === null ? '[GAGAL DEKRIPSI]' : e($judulP) ?></div><br>
          <div class="plain"><u><?= $displayText('Isi:') ?></u> <?= $isiP === null ? '[GAGAL DEKRIPSI]' : e($isiP) ?></div>
          <?php else: ?>
          <div class="muted"><?= $displayText('Konten plaintext tidak tersedia tanpa pasangan kunci yang valid.') ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php layout_foot();
