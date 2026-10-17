<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class CryptoController extends BaseController
{
    public function savePublicKey(): void
    {
        $this->requireLogin();
        $this->requirePostCsrf();
        $payload = json_decode(file_get_contents('php://input') ?: '', true);
        $publicKey = is_array($payload) ? trim((string) ($payload['public_key'] ?? '')) : '';

        header('Content-Type: application/json');
        if ($publicKey === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Missing public key']);
            return;
        }

        (new User())->savePublicKey((int) Auth::user()['id'], $publicKey);
        echo json_encode(['ok' => true]);
    }

    public function taskRecipients(?int $id = null): void
    {
        $this->requireLogin();
        $taskId = $id ?? (\App\Core\Validator::validateId($_GET['id'] ?? null) ?? 0);
        $taskModel = new Task();

        if ($taskId <= 0 || !$taskModel->canAccess(Auth::user(), $taskId)) {
            http_response_code(403);
            exit;
        }

        $this->jsonRecipients((new User())->recipientsForTask($taskId));
    }

    public function projectRecipients(?int $id = null): void
    {
        $this->requireLogin();
        $projectId = $id ?? (\App\Core\Validator::validateId($_GET['id'] ?? null) ?? 0);

        $projectModel = new Project();
        $project = $projectModel->find($projectId);
        $user = Auth::user();
        $canAccess = $project
            && (
                ($user['role'] ?? '') === 'admin'
                || (int) $project['created_by'] === (int) $user['id']
                || $projectModel->userBelongsToProject($projectId, (int) $user['id'])
            );

        if ($projectId <= 0 || !$canAccess) {
            http_response_code(403);
            exit;
        }

        $this->jsonRecipients((new User())->recipientsForProject($projectId));
    }

    public function currentRecipient(): void
    {
        $this->requireLogin();
        $this->jsonRecipients([(new User())->find((int) Auth::user()['id'])]);
    }

    private function jsonRecipients(array $users): void
    {
        header('Content-Type: application/json');
        $rows = [];
        foreach ($users as $user) {
            if (!$user || empty($user['public_key_jwk'])) {
                continue;
            }

            $rows[] = [
                'id' => (int) $user['id'],
                'name' => $user['name'],
                'public_key' => $user['public_key_jwk'],
            ];
        }

        echo json_encode(['recipients' => $rows]);
    }
}
