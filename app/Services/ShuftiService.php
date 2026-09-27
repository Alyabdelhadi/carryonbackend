<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin client for the Shufti Pro API (face + document verification),
 * onsite only: Shufti's own page captures a live selfie and the ID, see
 * [startOnsite].
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
     * Opens an onsite session: no proofs are sent, Shufti answers
     * `request.pending` with a `verification_url` where the user takes a
     * live selfie (liveness check) and scans the ID. The verdict arrives
     * later through the callback / [status].
     *
     * @return array{event: string, message: ?string, result: ?array, verification_url: ?string}
     */
    public function startOnsite(string $reference, ?string $email, string $language = 'EN'): array
    {
        $payload = [
            'reference' => $reference,
            'email' => $email ?? '',
            'country' => '',
            'language' => $language,
            'verification_mode' => 'image_only',
            // minutes before the verification_url stops working
            'ttl' => (int) config('services.shufti.onsite_ttl', 60),
            'show_results' => '1',
            // live camera capture only: no selfie from the gallery
            'face' => ['allow_offline' => '0', 'allow_online' => '1'],
            'document' => [
                'supported_types' => ['passport', 'id_card', 'driving_license'],
                'allow_offline' => '1',
                'allow_online' => '1',
            ],
        ];
        if (filled(config('services.shufti.callback_url'))) {
            $payload['callback_url'] = config('services.shufti.callback_url');
        }
        if (filled(config('services.shufti.redirect_url'))) {
            $payload['redirect_url'] = config('services.shufti.redirect_url');
        }

        // nothing user-supplied is checked yet, so any request.invalid is ours
        return $this->post('/', $payload, 30, false);
    }

    /** Current state of an earlier request, straight from Shufti. */
    public function status(string $reference): array
    {
        return $this->post('/status', ['reference' => $reference], 30);
    }

    private function post(string $path, array $payload, int $timeout, bool $proofsSent = true): array
    {
        if (!$this->isConfigured()) {
            Log::error('Shufti keys are missing (SHUFTI_CLIENT_ID / SHUFTI_SECRET_KEY).');
            return ['event' => self::EVENT_UNREACHABLE, 'message' => 'Verification is not configured.', 'result' => null, 'verification_url' => null];
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
                'verification_url' => null,
            ];
        }

        $body = $response->json();
        if (!is_array($body) || empty($body['event'])) {
            Log::warning('Shufti answered without an event.', ['path' => $path, 'status' => $response->status()]);
            return ['event' => self::EVENT_UNREACHABLE, 'message' => null, 'result' => null, 'verification_url' => null];
        }

        $error = $body['error'] ?? null;
        $event = (string) $body['event'];
        // request.invalid is about the user's photos only when Shufti names
        // the face or document service; anything else (callback domain,
        // payload, account setup) is our configuration, not theirs.
        if ($event === 'request.invalid'
            && (!$proofsSent || !in_array(is_array($error) ? ($error['service'] ?? '') : '', ['face', 'document'], true))) {
            Log::error('Shufti rejected the request configuration.', ['path' => $path, 'error' => $error]);
            $event = self::EVENT_CONFIG_ERROR;
        }
        return [
            'event' => $event,
            'message' => is_array($error) ? ($error['message'] ?? null) : ($body['declined_reason'] ?? null),
            // pass/fail flags only; verification_data (name, DOB, document
            // number) is personal data we do not need to keep
            'result' => is_array($body['verification_result'] ?? null) ? $body['verification_result'] : null,
            'verification_url' => is_string($body['verification_url'] ?? null) ? $body['verification_url'] : null,
        ];
    }
}
