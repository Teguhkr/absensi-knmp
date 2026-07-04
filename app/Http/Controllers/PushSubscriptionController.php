<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Simpan atau update subskripsi push browser pengguna.
     */
    public function store(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|url',
            'keys.auth' => 'required',
            'keys.p256dh' => 'required',
        ]);

        $user = Auth::user();
        
        // Simpan atau update subskripsi ke database
        $user->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'],
            $request->keys['auth']
        );

        return response()->json([
            'success' => true,
            'message' => 'Subscription saved successfully.'
        ]);
    }

    /**
     * Hapus subskripsi push browser (unsubscribe).
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|url',
        ]);

        $user = Auth::user();
        
        if ($user) {
            $user->deletePushSubscription($request->endpoint);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription deleted successfully.'
        ]);
    }
}
