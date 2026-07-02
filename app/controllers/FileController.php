<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Attachment;
use App\Models\Log;
use App\Models\Task;
use App\Services\FileCryptoService;

/**
 * Controller xu ly tai file sau khi kiem tra quyen truy cap.
 */
class FileController extends BaseController
{
    /**
     * Giai ma bang hybrid encryption va stream file cho nguoi dung hop le.
     */
    public function download(int $id): void
    {
        $this->requireLogin();

        $attachmentModel = new Attachment();
        $attachment = $attachmentModel->find($id);

        if (!$attachment) {
            exit('File khong ton tai.');
        }

        if (!(new Task())->canAccess(Auth::user(), (int) $attachment['task_id'])) {
            http_response_code(403);
            exit('Ban khong co quyen tai file nay.');
        }

        try {
            if (empty($attachment['encrypted_key']) || empty($attachment['iv'])) {
                throw new \RuntimeException('File nay khong co khoa ma hoa RSA/AES hop le.');
            }

            $cryptoService = new FileCryptoService();
            $content = $cryptoService->hybridDecrypt(
                $attachment['encrypted_path'],
                $cryptoService->getRsaPrivateKey(),
                $attachment['encrypted_key'],
                $attachment['iv']
            );

            (new Log())->create(Auth::user()['id'], 'download', 'attachment', $id, 'Tai file cong viec');

            header('Content-Description: File Transfer');
            header('Content-Type: ' . ($attachment['mime_type'] ?: 'application/octet-stream'));
            header('Content-Disposition: attachment; filename="' . basename($attachment['original_name']) . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
            exit;
        } catch (\Throwable $e) {
            exit('Khong the giai ma hoac tai file: ' . $e->getMessage());
        }
    }
}
