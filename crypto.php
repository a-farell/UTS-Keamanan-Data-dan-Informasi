<?php
declare(strict_types=1);

/* =========================================================
 * KRIPTOGRAFI SIMETRIS : AES-256-GCM
 * Format output: base64( IV(12 byte) + TAG(16 byte) + CIPHERTEXT )
 * ========================================================= */
function aes_key(string $symKey): string
{
    // Turunkan kunci 32 byte (256 bit) dari kunci simetris yang diinput admin
    return hash('sha256', $symKey, true);
}

function aes_encrypt(string $plain, string $symKey): string
{
    $iv  = random_bytes(12);
    $tag = '';
    $ct  = openssl_encrypt($plain, 'aes-256-gcm', aes_key($symKey), OPENSSL_RAW_DATA, $iv, $tag, '', 16);
    if ($ct === false) {
        throw new RuntimeException('Enkripsi AES gagal.');
    }
    return base64_encode($iv . $tag . $ct);
}

function aes_decrypt(string $b64, string $symKey): ?string
{
    $raw = base64_decode($b64, true);
    if ($raw === false || strlen($raw) < 28) {
        return null;
    }
    $iv  = substr($raw, 0, 12);
    $tag = substr($raw, 12, 16);
    $ct  = substr($raw, 28);
    $plain = openssl_decrypt($ct, 'aes-256-gcm', aes_key($symKey), OPENSSL_RAW_DATA, $iv, $tag);
    return $plain === false ? null : $plain;
}

/* =========================================================
 * KRIPTOGRAFI ASIMETRIS : RSA-2048
 * - Login  : challenge-response (enkripsi RSA-OAEP dengan public key,
 *            dekripsi dengan private key yang diinput admin)
 * - Simpan : tanda tangan digital RSA-SHA256 pada data terenkripsi
 * ========================================================= */
function openssl_extra(): array
{
    // Di Windows (XAMPP / Laragon), OpenSSL kadang butuh file openssl.cnf
    $cands = [getenv('OPENSSL_CONF') ?: ''];
    $ini = php_ini_loaded_file();
    if ($ini) {
        $cands[] = dirname($ini) . '/extras/ssl/openssl.cnf';
    }
    $cands[] = 'C:/xampp/php/extras/ssl/openssl.cnf';
    $cands[] = 'C:/xampp/apache/conf/openssl.cnf';
    foreach (['C:/laragon/bin/php/*/extras/ssl/openssl.cnf', 'C:/laragon/bin/apache/*/conf/openssl.cnf'] as $pattern) {
        foreach (glob($pattern) ?: [] as $f) {
            $cands[] = $f;
        }
    }
    foreach ($cands as $c) {
        if ($c !== '' && is_file($c)) {
            return ['config' => $c];
        }
    }
    return [];
}

function rsa_generate(): array
{
    $opts = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA] + openssl_extra();
    $res = openssl_pkey_new($opts);
    if ($res === false) {
        throw new RuntimeException('Gagal membuat kunci RSA: ' . openssl_error_string());
    }
    openssl_pkey_export($res, $priv, null, openssl_extra());
    $pub = openssl_pkey_get_details($res)['key'];
    return [$priv, $pub];
}

function verify_private_key(string $privPem, string $pubPem): bool
{
    $pk = @openssl_pkey_get_private($privPem);
    if ($pk === false) {
        return false;
    }
    $nonce = random_bytes(32);
    if (!openssl_public_encrypt($nonce, $enc, $pubPem, OPENSSL_PKCS1_OAEP_PADDING)) {
        return false;
    }
    if (!@openssl_private_decrypt($enc, $dec, $pk, OPENSSL_PKCS1_OAEP_PADDING)) {
        return false;
    }
    return hash_equals($nonce, $dec);
}

function rsa_sign(string $data, string $privPem): string
{
    $pk = openssl_pkey_get_private($privPem);
    if ($pk === false || !openssl_sign($data, $sig, $pk, OPENSSL_ALGO_SHA256)) {
        throw new RuntimeException('Gagal membuat tanda tangan digital.');
    }
    return base64_encode($sig);
}

function rsa_verify(string $data, string $sigB64, string $pubPem): bool
{
    $sig = base64_decode($sigB64, true);
    return $sig !== false && openssl_verify($data, $sig, $pubPem, OPENSSL_ALGO_SHA256) === 1;
}
