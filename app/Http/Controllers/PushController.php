<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Services\FirebaseService; // Import the FirebaseService

class PushController extends Controller
{
    protected $firebaseService;
    public $folder  = "push.";

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function index()
    {							
        return view($this->folder . 'index');
    }

    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'text' => 'required|string',
        ]);

        $title = $request->input('title');
        $body = $request->input('text');

        // Send notification to all users subscribed to the 'carryon' topic
        $response = $this->firebaseService->sendToTopic('carryon', $title, $body);

        return Redirect::back()->with('message', 'Push Notification Sent Successfully')
                               ->with('response', $response);
    }

    public function sendToUser(Request $request, $userId)
    {
        $request->validate([
            'title' => 'required|string',
            'text' => 'required|string',
        ]);

        $title = $request->input('title');
        $body = $request->input('text');

        // Send notification to the user-specific topic 'user_{userId}'
        $response = $this->firebaseService->sendToUser($userId, $title, $body);

        return Redirect::back()->with('message', 'Push Notification Sent to User Successfully')
                               ->with('response', $response);
    }
}