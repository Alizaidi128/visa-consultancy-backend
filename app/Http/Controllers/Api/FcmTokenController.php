<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device' => 'required|string',
        ]);

        $user = $request->user();

        \App\Models\FcmToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'device'  => $request->device,
            ],
            [
                'token' => $request->token,
            ]
        );

        return response()->json([
            'message' => 'FCM token saved',
        ]);
    }


    public function destroy(Request $request)
    {
        $user = $request->user();

        \App\Models\FcmToken::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'FCM token deleted',
        ]);
    }


}
