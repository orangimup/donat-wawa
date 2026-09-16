<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationSettingsController extends Controller
{
    public function show(Request $request): View
    {
        return view('user.settings.notifications', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->notif_order_status = $request->boolean('notif_order_status');
        $user->notif_order_confirmation = $request->boolean('notif_order_confirmation');
        $user->notif_review_reminder = $request->boolean('notif_review_reminder');
        $user->notif_new_product = $request->boolean('notif_new_product');
        $user->notif_daily_reminder = $request->boolean('notif_daily_reminder');
        $user->save();

        return redirect()->route('settings.notifications')->with('status', 'Preferensi notifikasi berhasil disimpan.');
    }
}