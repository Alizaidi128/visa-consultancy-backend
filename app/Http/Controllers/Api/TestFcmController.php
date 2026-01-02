<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FcmService;

class TestFcmController extends Controller
{
    public function send()
    {
        $user = auth()->user();

        $tokens = $user->fcmTokens()->pluck('token')->toArray();

        app(FcmService::class)->send(
            $tokens,
            'Test Push',
            'Firebase push is working 🚀',
            ['type' => 'test']
        );

        return response()->json(['sent' => true]);
    }
}
