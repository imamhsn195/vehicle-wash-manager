<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\Process\Process;

class DeployWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = (string) (
            config('deploy.webhook_secret')
            ?: env('DEPLOY_WEBHOOK_SECRET', '')
        );

        if ($secret === '') {
            return response('Webhook not configured', 503);
        }

        $signature = (string) $request->header('X-Hub-Signature-256', '');
        $payload = $request->getContent();
        $expected = 'sha256='.hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expected, $signature)) {
            return response('Invalid signature', 403);
        }

        $event = (string) $request->header('X-GitHub-Event', '');
        if ($event !== 'push' && $event !== 'ping') {
            return response('Ignored event', 200);
        }

        if ($event === 'ping') {
            return response('pong', 200);
        }

        $branch = (string) config('deploy.branch', 'cursor/vehicle-wash-phase1-7c7f');
        if ($override = $this->readDeployEnv('DEPLOY_BRANCH')) {
            $branch = $override;
        }

        $ref = (string) data_get($request->json()->all(), 'ref', '');
        if ($ref !== '' && $ref !== 'refs/heads/'.$branch) {
            return response("Ignored ref {$ref}", 200);
        }

        if (! function_exists('exec') && ! function_exists('shell_exec') && ! class_exists(Process::class)) {
            return response('Shell execution disabled — use cron instead', 500);
        }

        $script = base_path('scripts/cpanel-deploy.sh');
        if (! is_file($script)) {
            return response('Deploy script missing', 500);
        }

        $process = Process::fromShellCommandline(
            'bash '.escapeshellarg($script),
            base_path(),
            null,
            null,
            600
        );
        $process->start();

        return response('Deploy started', 202);
    }

    protected function readDeployEnv(string $key): ?string
    {
        $path = base_path('.env.deploy');
        if (! is_readable($path)) {
            return null;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            if (trim($k) === $key) {
                return trim($v, " \t\"'");
            }
        }

        return null;
    }
}
