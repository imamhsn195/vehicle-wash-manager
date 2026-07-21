<?php

namespace Tests\Feature;

use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class DeployWebhookTest extends TestCase
{
    public function test_webhook_rejects_when_secret_missing(): void
    {
        config(['deploy.webhook_secret' => null]);

        $this->postJson('/deploy/webhook', [])
            ->assertStatus(503);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        config(['deploy.webhook_secret' => 'test-secret']);

        $this->postJson('/deploy/webhook', ['ref' => 'refs/heads/main'], [
            'X-Hub-Signature-256' => 'sha256=invalid',
            'X-GitHub-Event' => 'push',
        ])->assertStatus(403);
    }

    public function test_webhook_pong_on_ping(): void
    {
        config(['deploy.webhook_secret' => 'test-secret']);
        $payload = json_encode(['zen' => 'ok']);
        $sig = 'sha256='.hash_hmac('sha256', $payload, 'test-secret');

        $this->call(
            'POST',
            '/deploy/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => $sig,
                'HTTP_X_GITHUB_EVENT' => 'ping',
            ],
            $payload
        )->assertOk()->assertSee('pong');
    }

    public function test_webhook_ignores_other_branches(): void
    {
        config(['deploy.webhook_secret' => 'test-secret']);
        $payload = json_encode(['ref' => 'refs/heads/other-branch']);
        $sig = 'sha256='.hash_hmac('sha256', $payload, 'test-secret');

        $this->call(
            'POST',
            '/deploy/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => $sig,
                'HTTP_X_GITHUB_EVENT' => 'push',
            ],
            $payload
        )->assertOk()->assertSee('Ignored ref');
    }
}
