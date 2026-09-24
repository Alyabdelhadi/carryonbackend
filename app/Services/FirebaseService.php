<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseService
{
    protected $messaging = null;

    public function __construct()
    {
        // FIREBASE_ENABLED=false (local development) turns every send into a
        // log line instead of a real push to production topics.
        if (!config('services.firebase.enabled', true)) {
            return;
        }

        $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
        $this->messaging = $factory->createMessaging();
    }

    /** @param array $data optional key/value strings the app reads on tap (e.g. order_id) */
    public function sendToTopic($topic, $title, $body, array $data = [])
    {
        if ($this->messaging === null) {
            Log::info("Push disabled: skipped topic '{$topic}' — {$title}: {$body}");
            return null;
        }

        $payload = [
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
        ];
        if (!empty($data)) {
            $payload['data'] = array_map('strval', $data);
        }
        $message = CloudMessage::fromArray($payload);

        $response = $this->messaging->send($message);
        return $response;
    }

    public function sendToUser($userId, $title, $body, array $data = [])
    {
        $topic = 'user_' . $userId; // Firebase Topic for user
        return $this->sendToTopic($topic, $title, $body, $data);
    }
}
