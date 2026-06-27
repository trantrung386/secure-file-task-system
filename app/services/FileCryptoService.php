<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * Service mã hóa/giải mã file bằng AES-256-CBC.
 */
class FileCryptoService
{
    private const RSA_PRIVATE_KEY_FILE = STORAGE_PATH . '/rsa_private.pem';
    private const RSA_PUBLIC_KEY_FILE = STORAGE_PATH . '/rsa_public.pem';

    /**
     * Sinh khóa nhị phân từ APP_KEY để dùng cho OpenSSL.
     */
    private function key(): string
    {
        return hash('sha256', APP_KEY, true);
    }

    /**
     * Đảm bảo RSA keypair tồn tại, nếu không thì tạo mới.
     */
    public function ensureRsaKeysExist(): void
    {
        if (file_exists(self::RSA_PRIVATE_KEY_FILE) && file_exists(self::RSA_PUBLIC_KEY_FILE)) {
            return;
        }

        $keyPair = $this->generateRsaKeyPair(2048);
        
        $privateWritten = file_put_contents(self::RSA_PRIVATE_KEY_FILE, $keyPair['private_key']);
        $publicWritten = file_put_contents(self::RSA_PUBLIC_KEY_FILE, $keyPair['public_key']);

        if ($privateWritten === false || $publicWritten === false) {
            throw new RuntimeException('Không thể lưu RSA keypair vào storage.');
        }

        // Đặt permission để bảo vệ private key
        chmod(self::RSA_PRIVATE_KEY_FILE, 0600);
    }

    /**
     * Lấy RSA public key để mã hóa AES key.
     */
    public function getRsaPublicKey(): string
    {
        $this->ensureRsaKeysExist();

        $content = file_get_contents(self::RSA_PUBLIC_KEY_FILE);
        if ($content === false) {
            throw new RuntimeException('Không thể đọc RSA public key.');
        }

        return $content;
    }

    /**
     * Lấy RSA private key để giải mã AES key (chỉ dùng server-side).
     */
    public function getRsaPrivateKey(): string
    {
        $this->ensureRsaKeysExist();

        $content = file_get_contents(self::RSA_PRIVATE_KEY_FILE);
        if ($content === false) {
            throw new RuntimeException('Không thể đọc RSA private key.');
        }

        return $content;
    }

    /**
     * Mã hóa file tạm và lưu thành file .bin trong storage/encrypted.
     */
    public function encryptAndStore(string $tmpPath, string $originalName): array
    {
        $content = file_get_contents($tmpPath);
        if ($content === false) {
            throw new RuntimeException('Không đọc được file tải lên.');
        }

        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($content, 'AES-256-CBC', $this->key(), OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            throw new RuntimeException('Mã hóa file thất bại.');
        }

        $storedName = uniqid('enc_', true) . '.bin';
        $path = STORAGE_PATH . '/encrypted/' . $storedName;
        $bytes = file_put_contents($path, $iv . $encrypted);

        if ($bytes === false) {
            throw new RuntimeException('Không lưu được file đã mã hóa.');
        }

        return [
            'stored_name' => $storedName,
            'path' => $path,
        ];
    }

    /**
     * Giải mã file đã lưu trong storage khi người dùng hợp lệ yêu cầu tải xuống.
     */
    public function decrypt(string $path): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException('File không tồn tại.');
        }

        $raw = file_get_contents($path);
        if ($raw === false || strlen($raw) <= 16) {
            throw new RuntimeException('Dữ liệu file không hợp lệ.');
        }

        $iv = substr($raw, 0, 16);
        $encrypted = substr($raw, 16);
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $this->key(), OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new RuntimeException('Giải mã file thất bại.');
        }

        return $decrypted;
    }

    /**
     * Sinh cặp khóa RSA (private/public) để dùng cho mã hóa lai.
     */
    public function generateRsaKeyPair(int $bits = 2048): array
    {
        $config = [
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        if ($configPath = $this->getOpenSslConfigPath()) {
            $config['config'] = $configPath;
        }

        $resource = openssl_pkey_new($config);
        if ($resource === false) {
            $error = openssl_error_string();
            throw new RuntimeException('Tạo cặp khóa RSA thất bại.' . ($error ? ' Lỗi OpenSSL: ' . $error : ''));
        }

        $privateKey = '';
        if (!openssl_pkey_export($resource, $privateKey, null, $config)) {
            $error = openssl_error_string();
            throw new RuntimeException('Xuất khóa RSA private thất bại.' . ($error ? ' Lỗi OpenSSL: ' . $error : ''));
        }

        $details = openssl_pkey_get_details($resource);
        if ($details === false || empty($details['key'])) {
            if (PHP_VERSION_ID < 80000) openssl_free_key($resource);
            throw new RuntimeException('Xuất khóa RSA public thất bại.');
        }

        if (PHP_VERSION_ID < 80000) openssl_free_key($resource);

        return [
            'private_key' => $privateKey,
            'public_key' => $details['key'],
        ];
    }

    /**
     * Tìm file cấu hình OpenSSL trên hệ thống để hỗ trợ tạo khóa RSA.
     */
    private function getOpenSslConfigPath(): ?string
    {
        $paths = [
            getenv('OPENSSL_CONF'),
            getenv('SSLEAY_CONF'),
            'C:\\xampp\\php\\extras\\openssl\\openssl.cnf',
            'C:\\xampp\\php\\extras\\ssl\\openssl.cnf',
            'C:\\xampp\\apache\\conf\\openssl.cnf',
        ];

        foreach ($paths as $path) {
            if (is_string($path) && $path !== '' && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Mã hóa lai: mã hóa nội dung file bằng AES-256-CBC, sau đó mã hóa AES key bằng RSA public.
     */
    public function hybridEncryptAndStore(string $tmpPath, string $originalName, string $rsaPublicKeyPem): array
    {
        $content = file_get_contents($tmpPath);
        if ($content === false) {
            throw new RuntimeException('Không đọc được file tải lên.');
        }

        $aesKey = random_bytes(32);
        $iv = random_bytes(16);

        $ciphertext = openssl_encrypt($content, 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            throw new RuntimeException('Mã hóa AES thất bại.');
        }

        $publicKey = openssl_pkey_get_public($rsaPublicKeyPem);
        if ($publicKey === false) {
            throw new RuntimeException('Khóa công khai RSA không hợp lệ.');
        }

        $encryptedKey = '';
        $ok = openssl_public_encrypt($aesKey, $encryptedKey, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);
        openssl_free_key($publicKey);
        if ($ok === false) {
            throw new RuntimeException('Mã hóa khóa AES bằng RSA thất bại.');
        }

        $storedName = uniqid('enc_', true) . '.bin';
        $path = STORAGE_PATH . '/encrypted/' . $storedName;
        $bytes = file_put_contents($path, $iv . $ciphertext);

        if ($bytes === false) {
            throw new RuntimeException('Không lưu được file đã mã hóa.');
        }

        return [
            'stored_name' => $storedName,
            'path' => $path,
            'key' => base64_encode($encryptedKey),
            'iv' => base64_encode($iv),
            'original_name' => $originalName,
        ];
    }

    /**
     * Giải mã file đã lưu bằng khóa RSA private và AES key được mã hóa.
     */
    public function hybridDecrypt(string $path, string $rsaPrivateKeyPem, string $encryptedKeyB64, string $ivB64): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException('File không tồn tại.');
        }

        $raw = file_get_contents($path);
        if ($raw === false || strlen($raw) <= 16) {
            throw new RuntimeException('Dữ liệu file không hợp lệ.');
        }

        $encryptedKey = base64_decode($encryptedKeyB64, true);
        $iv = base64_decode($ivB64, true);
        if ($encryptedKey === false || $iv === false) {
            throw new RuntimeException('Dữ liệu mã hóa RSA/AES không hợp lệ.');
        }

        $privateKey = openssl_pkey_get_private($rsaPrivateKeyPem);
        if ($privateKey === false) {
            throw new RuntimeException('Khóa riêng RSA không hợp lệ.');
        }

        $aesKey = '';
        $ok = openssl_private_decrypt($encryptedKey, $aesKey, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);
        openssl_free_key($privateKey);
        if ($ok === false) {
            throw new RuntimeException('Giải mã khóa AES bằng RSA thất bại.');
        }

        $ciphertext = substr($raw, 16);
        if ($ciphertext === false) {
            throw new RuntimeException('Dữ liệu mã hóa AES không hợp lệ.');
        }

        $plaintext = openssl_decrypt($ciphertext, 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA, $iv);
        if ($plaintext === false) {
            throw new RuntimeException('Giải mã AES thất bại.');
        }

        return $plaintext;
    }
}
