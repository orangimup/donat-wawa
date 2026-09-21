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
            'preference' => $request->user()->notificationPreferenceOrDefault(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $preference = $request->user()->notificationPreferenceOrDefault();

        $preference->update([
            'notif_order_status' => $request->boolean('notif_order_status'),
            'notif_order_confirmation' => $request->boolean('notif_order_confirmation'),
            'notif_review_reminder' => $request->boolean('notif_review_reminder'),
            'notif_new_product' => $request->boolean('notif_new_product'),
            'notif_daily_reminder' => $request->boolean('notif_daily_reminder'),
        ]);

        return redirect()->route('settings.notifications')->with('status', __('Notification preferences saved successfully.'));
    }
}