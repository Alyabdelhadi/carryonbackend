<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin client for the Shufti Pro API (face + document verification).
 * Returns plain arrays and never throws: every failure comes back as an
 * event the caller can store.
 */
class ShuftiService
{
    /** No verdict: we could not reach Shufti or it refused our keys. */
    public const EVENT_UNREACHABLE = 'unreachable';

    /** Shufti refused the request itself (not the photos): fix the setup. */
    public const EVENT_CONFIG_ERROR = 'config_error';

    /** The request went out but Shufti did not answer in time. */
    public const EVENT_TIMEOUT = 'timeout';

    public function isConfigured(): bool
    {
        return filled(config('services.shufti.client_id')) && filled(config('services.shufti.secret_key'));
    }

    /**
     * @return array{event: string, message: ?string, result: ?array}
     */
    public function verify(string $reference, string $selfiePath, string $identityPath, ?string $email): array
    {
        $payload = [
            'reference' => $reference,
            'email' => $email ?? '',
            'country' => '',
            'language' => 'EN',
            'face' => ['proof' => base64_encode(file_get_contents($selfiePath))],
            'document' => [
                'proof' => base64_encode(file_get_contents($identityPath)),
                'supported_types' => ['passport', 'id_card', 'driving_license'],
            ],
        ];
        if (filled(config('services.shufti.callback_url'))) {
            $payload['callback_url'] = config('services.shufti.callback_url');
        }

        return $this->post('/', $payload, (int) config('services.shufti.timeout', 60));
    }

    /** Current state of an earlier request, straight from Shufti. */
    public function status(string $reference): array
    {
        return $this->post('/status', ['reference' => $reference], 30);
    }

    private function post(string $path, array $payload, int $timeout): array
    {
        if (!$this->isConfigured()) {
            Log::error('Shufti keys are missing (SHUFTI_CLIENT_ID / SHUFTI_SECRET_KEY).');
            return ['event' => self::EVENT_UNREACHABLE, 'message' => 'Verification is not configured.', 'result' => null];
        }

        try {
            $response = Http::withBasicAuth(config('services.shufti.client_id'), config('services.shufti.secret_key'))
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout($timeout)
                ->post(rtrim(config('services.shufti.base_url'), '/') . $path, $payload);
        } catch (ConnectionException $e) {
            // cURL error 28 is a timeout; after the connect phase the request
            // reached Shufti and may still be processed (callback / sync).
            $timedOut = str_contains($e->getMessage(), 'cURL error 28')
                && !str_contains($e->getMessage(), 'Connection timed out')
                && !str_contains($e->getMessage(), 'Resolving timed out');
            Log::warning('Shufti request failed.', ['path' => $path, 'reference' => $payload['reference'] ?? null, 'error' => $e->getMessage()]);
            return [
                'event' => $timedOut ? self::EVENT_TIMEOUT : self::EVENT_UNREACHABLE,
                'message' => null,
                'result' => null,
            ];
        }

        $body = $response->json();
        if (!is_array($body) || empty($body['event'])) {
            Log::warning('Shufti answered without an event.', ['path' => $path, 'status' => $response->status()]);
            return ['event' => self::EVENT_UNREACHABLE, 'message' => null, 'result' => null];
        }

        $error = $body['error'] ?? null;
        $event = (string) $body['event'];
        // request.invalid is about the user's photos only when Shufti names
        // the face or document service; anything else (callback domain,
        // payload, account setup) is our configuration, not theirs.
        if ($event === 'request.invalid'
            && !in_array(is_array($error) ? ($error['service'] ?? '') : '', ['face', 'document'], true)) {
            Log::error('Shufti rejected the request configuration.', ['path' => $path, 'error' => $error]);
            $event = self::EVENT_CONFIG_ERROR;
        }
        return [
            'event' => $event,
            'message' => is_array($error) ? ($error['message'] ?? null) : ($body['declined_reason'] ?? null),
            // pass/fail flags only; verification_data (name, DOB, document
            // number) is personal data we do not need to keep
            'result' => is_array($body['verification_result'] ?? null) ? $body['verification_result'] : null,
        ];
    }
}
