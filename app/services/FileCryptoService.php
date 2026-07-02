<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * Service ma hoa/giai ma file bang hybrid encryption RSA + AES.
 */
class FileCryptoService
{
    private const RSA_PRIVATE_KEY_FILE = STORAGE_PATH . '/rsa_private.pem';
    private const RSA_PUBLIC_KEY_FILE = STORAGE_PATH . '/rsa_public.pem';

    /**
     * Dam bao RSA keypair ton tai, neu chua co thi tao moi.
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
            throw new RuntimeException('Khong the luu RSA keypair vao storage.');
        }

        chmod(self::RSA_PRIVATE_KEY_FILE, 0600);
    }

    /**
     * Lay RSA public key de ma hoa AES key.
     */
    public function getRsaPublicKey(): string
    {
        $this->ensureRsaKeysExist();

        $content = file_get_contents(self::RSA_PUBLIC_KEY_FILE);
        if ($content === false) {
            throw new RuntimeException('Khong the doc RSA public key.');
        }

        return $content;
    }

    /**
     * Lay RSA private key de giai ma AES key tren server.
     */
    public function getRsaPrivateKey(): string
    {
        $this->ensureRsaKeysExist();

        $content = file_get_contents(self::RSA_PRIVATE_KEY_FILE);
        if ($content === false) {
            throw new RuntimeException('Khong the doc RSA private key.');
        }

        return $content;
    }

    /**
     * Sinh cap khoa RSA private/public cho ma hoa lai.
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
            throw new RuntimeException('Tao cap khoa RSA that bai.' . ($error ? ' Loi OpenSSL: ' . $error : ''));
        }

        $privateKey = '';
        if (!openssl_pkey_export($resource, $privateKey, null, $config)) {
            $error = openssl_error_string();
            throw new RuntimeException('Xuat khoa RSA private that bai.' . ($error ? ' Loi OpenSSL: ' . $error : ''));
        }

        $details = openssl_pkey_get_details($resource);
        if ($details === false || empty($details['key'])) {
            if (PHP_VERSION_ID < 80000) {
                openssl_free_key($resource);
            }
            throw new RuntimeException('Xuat khoa RSA public that bai.');
        }

        if (PHP_VERSION_ID < 80000) {
            openssl_free_key($resource);
        }

        return [
            'private_key' => $privateKey,
            'public_key' => $details['key'],
        ];
    }

    /**
     * Tim file cau hinh OpenSSL tren he thong, huu ich khi chay bang XAMPP.
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
     * Ma hoa lai: file bang AES-256-CBC, AES key bang RSA public key.
     */
    public function hybridEncryptAndStore(string $tmpPath, string $originalName, string $rsaPublicKeyPem): array
    {
        $content = file_get_contents($tmpPath);
        if ($content === false) {
            throw new RuntimeException('Khong doc duoc file tai len.');
        }

        $aesKey = random_bytes(32);
        $iv = random_bytes(16);

        $ciphertext = openssl_encrypt($content, 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            throw new RuntimeException('Ma hoa AES that bai.');
        }

        $publicKey = openssl_pkey_get_public($rsaPublicKeyPem);
        if ($publicKey === false) {
            throw new RuntimeException('Khoa cong khai RSA khong hop le.');
        }

        $encryptedKey = '';
        $ok = openssl_public_encrypt($aesKey, $encryptedKey, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);
        openssl_free_key($publicKey);
        if ($ok === false) {
            throw new RuntimeException('Ma hoa khoa AES bang RSA that bai.');
        }

        $storedName = uniqid('enc_', true) . '.bin';
        $path = STORAGE_PATH . '/encrypted/' . $storedName;
        $bytes = file_put_contents($path, $iv . $ciphertext);

        if ($bytes === false) {
            throw new RuntimeException('Khong luu duoc file da ma hoa.');
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
     * Giai ma file bang RSA private key va AES key da duoc boc RSA.
     */
    public function hybridDecrypt(string $path, string $rsaPrivateKeyPem, string $encryptedKeyB64, string $ivB64): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException('File khong ton tai.');
        }

        $raw = file_get_contents($path);
        if ($raw === false || strlen($raw) <= 16) {
            throw new RuntimeException('Du lieu file khong hop le.');
        }

        $encryptedKey = base64_decode($encryptedKeyB64, true);
        $iv = base64_decode($ivB64, true);
        if ($encryptedKey === false || $iv === false || strlen($iv) !== 16) {
            throw new RuntimeException('Du lieu ma hoa RSA/AES khong hop le.');
        }

        $privateKey = openssl_pkey_get_private($rsaPrivateKeyPem);
        if ($privateKey === false) {
            throw new RuntimeException('Khoa rieng RSA khong hop le.');
        }

        $aesKey = '';
        $ok = openssl_private_decrypt($encryptedKey, $aesKey, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);
        openssl_free_key($privateKey);
        if ($ok === false) {
            throw new RuntimeException('Giai ma khoa AES bang RSA that bai.');
        }

        $ciphertext = substr($raw, 16);
        if ($ciphertext === '') {
            throw new RuntimeException('Du lieu ma hoa AES khong hop le.');
        }

        $plaintext = openssl_decrypt($ciphertext, 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA, $iv);
        if ($plaintext === false) {
            throw new RuntimeException('Giai ma AES that bai.');
        }

        return $plaintext;
    }
}
