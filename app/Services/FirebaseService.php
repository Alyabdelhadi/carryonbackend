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

    public function sendToTopic($topic, $title, $body)
    {
        if ($this->messaging === null) {
            Log::info("Push disabled: skipped topic '{$topic}' — {$title}: {$body}");
            return null;
        }

        $message = CloudMessage::fromArray([
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
        ]);

        $response = $this->messaging->send($message);
        return $response;
    }

    public function sendToUser($userId, $title, $body)
    {
        $topic = 'user_' . $userId; // Firebase Topic for user
        return $this->sendToTopic($topic, $title, $body);
    }
}
