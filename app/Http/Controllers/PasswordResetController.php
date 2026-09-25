<?php

namespace App\Http\Controllers;

use App\Services\PasswordResetService;
use Illuminate\Http\Request;

/** api/password/* : reset by emailed code (the app's Forgot password flow). */
class PasswordResetController extends Controller
{
    public function __construct(private PasswordResetService $reset)
    {
    }

    /** POST api/password/request {email, lang?} */
    public function request(Request $request)
    {
        return response()->json($this->reset->request($request->input('email'), (string) $request->input('lang', 'en')));
    }

    /** POST api/password/verify {email, code} */
    public function verify(Request $request)
    {
        return response()->json($this->reset->verify($request->input('email'), (string) $request->input('code')));
    }

    /** POST api/password/reset {user_id, token, password} */
    public function reset(Request $request)
    {
        return response()->json($this->reset->reset($request->input('user_id'), $request->input('token'), $request->input('password')));
    }
}
