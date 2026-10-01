<?php
/**
 * Encrypted, bounded demo-session storage for stateless hosts.
 * No file writes or database. Native PHP sessions remain in use on localhost.
 * Cookie snapshots are not a real payment ledger or concurrent shared database.
 */
declare(strict_types=1);
final class TabungCookieSession implements SessionHandlerInterface
{
    private const PARTS = 3;
    private const CHUNK = 2800;
    private const TTL = 14400; // Four hours.
    private $key;
    private $secure;

    public function __construct(string $secret, bool $secure)
    {
        $this->key = hash('sha256', $secret, true);
        $this->secure = $secure;
    }
    public function open($path, $name): bool { return true; }
    public function close(): bool { return true; }
    public function gc($maxLifetime): int { return 0; }
    private function cookie(string $name, string $value, int $expiry): void
    {
        setcookie($name, $value, ['expires'=>$expiry, 'path'=>'/', 'secure'=>$this->secure,
            'httponly'=>true, 'samesite'=>'Lax']);
    }
    public function destroy($id): bool
    {
        for ($i=0; $i<self::PARTS; $i++) $this->cookie('ty_state_'.$i, '', time()-3600);
        return true;
    }
    public function read($id): string
    {
        $encoded = '';
        for ($i=0; $i<self::PARTS; $i++) {
            $part = $_COOKIE['ty_state_'.$i] ?? '';
            if (!is_string($part) || strlen($part)>self::CHUNK) return '';
            $encoded .= $part;
        }
        if ($encoded === '') return '';
        $raw = base64_decode(strtr($encoded, '-_', '+/'), true);
        if ($raw === false || strlen($raw)<29) return '';
        $packed = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', $this->key,
            OPENSSL_RAW_DATA, substr($raw,0,12), substr($raw,12,16), 'tabung-v1:'.$id);
        if ($packed === false) return '';
        $plain = @gzinflate($packed, 65536);
        if (!is_string($plain) || strlen($plain)<11 || $plain[10]!==':' || !ctype_digit(substr($plain,0,10))) return '';
        $expires = (int)substr($plain,0,10);
        if ($expires < time() || $expires > time()+self::TTL+60) return '';
        return substr($plain,11);
    }
    public function write($id, $data): bool
    {
        // Do not overwrite the previous valid snapshot when the payload is too large.
        $packed = gzdeflate((time()+self::TTL).':'.$data, 6);
        if ($packed === false) return $this->storageError();
        $iv = random_bytes(12); $tag = '';
        $cipher = openssl_encrypt($packed, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA,
            $iv, $tag, 'tabung-v1:'.$id);
        if ($cipher === false) return $this->storageError();
        $encoded = rtrim(strtr(base64_encode($iv.$tag.$cipher), '+/', '-_'), '=');
        if (strlen($encoded) > self::PARTS*self::CHUNK) return $this->storageError();
        $chunks = str_split($encoded, self::CHUNK);
        for ($i=0; $i<self::PARTS; $i++) {
            $this->cookie('ty_state_'.$i, $chunks[$i] ?? '', isset($chunks[$i]) ? time()+self::TTL : time()-3600);
        }
        return true;
    }
    private function storageError(): bool
    {
        // The app buffers output and closes its session before headers are sent.
        header_remove('Location'); http_response_code(413);
        header('Content-Type: text/html; charset=utf-8');
        if (ob_get_level()) ob_clean();
        echo '<!doctype html><html lang="id"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Kapasitas demo penuh</title><link rel="stylesheet" href="/assets/style.css"><main class="panel" style="max-width:540px;margin:10vh auto"><h1>Kapasitas demo penuh</h1><p>Perubahan terakhir belum disimpan. Data sebelumnya tetap tersedia. Gunakan keterangan yang lebih pendek, atau keluar untuk memulai sesi baru.</p><a class="btn primary" href="/beranda.php">Kembali ke beranda</a></main></html>';
        return true;
    }
}
