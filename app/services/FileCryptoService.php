<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * E2E mode: server chi luu ciphertext, khong nam khoa giai ma.
 */
class FileCryptoService
{
    public function captureCiphertextUpload(string $tmpPath): array
    {
        if (!is_uploaded_file($tmpPath) && !is_file($tmpPath)) {
            throw new RuntimeException('File upload khong hop le.');
        }

        $storedName = uniqid('e2e_', true) . '.bin';
        $ciphertext = file_get_contents($tmpPath);
        if ($ciphertext === false) {
            throw new RuntimeException('Khong doc duoc ciphertext upload.');
        }

        return [
            'stored_name' => $storedName,
            'ciphertext' => $ciphertext,
        ];
    }

    public function storeCiphertextUpload(string $tmpPath): array
    {
        return $this->captureCiphertextUpload($tmpPath);
    }
}
