<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Notifications\CustomerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->where('type', CustomerNotification::class)
            ->latest()
            ->limit(15)
            ->get();

        $orders = Order::query()
            ->whereIn('id', $notifications->map(fn ($n) => $n->data['order_id'] ?? null)->filter()->unique())
            ->with('items.menuItem')
            ->get()
            ->keyBy('id');

        $items = $notifications
            ->map(function ($notification) use ($orders) {
                $row = CustomerNotification::present($notification);
                $order = $orders->get((int) ($notification->data['order_id'] ?? 0));

                if ($order === null) {
                    return $row;
                }

                $summary = $this->summary($order);
                $code = (string) ($notification->data['code'] ?? '');
                $line = trim($summary . ($summary !== '' && $code !== '' ? ' · ' : '') . $code);

                return array_merge($row, [
                    'body' => $line !== '' ? $line : $row['body'],
                    'note' => $row['event'] === 'order_rejected' ? (trim((string) ($notification->data['reason'] ?? '')) ?: null) : null,
                ]);
            })
            ->values();

        return response()
            ->json([
                'now' => now()->timestamp,
                'unread' => $user->unreadNotifications()->where('type', CustomerNotification::class)->count(),
                'items' => $items,
            ])
            ->header('Cache-Control', 'no-store');
    }

    private function summary(Order $order): string
    {
        $summary = $order->items->take(2)
            ->map(fn ($item) => $item->name . ' ×' . $item->quantity)
            ->implode(', ');

        $rest = $order->items->count() - 2;

        return $rest > 0 ? $summary . ' +' . $rest : $summary;
    }

    public function open(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->first();

        abort_if($notification === null, 404);

        $notification->markAsRead();

        $url = (string) ($notification->data['url'] ?? '');

        if (! Str::startsWith($url, '/') || Str::startsWith($url, '//')) {
            $url = route('settings.orders', [], false);
        }

        return redirect($url);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications()
            ->where('type', CustomerNotification::class)
            ->update(['read_at' => now()]);

        return response()->json(['unread' => 0]);
    }
}