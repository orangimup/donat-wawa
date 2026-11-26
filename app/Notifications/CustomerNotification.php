<?php

namespace App\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notification;

/**
 * Everything a customer can be notified about.
 *
 * The Settings > Notifications toggles are enforced HERE, in via(): when the matching toggle is off,
 * via() returns [] and Laravel stores nothing, so nothing ever reaches the snackbar or the bell.
 */
class CustomerNotification extends Notification
{
    /**
     * event => [preference column, icon].
     * A null column means "always send" (the customer has to act on it, e.g. enter a bank account).
     */
    public const EVENTS = [
        'order_process' => ['notif_order_confirmation', 'process'],
        'order_rejected' => ['notif_order_confirmation', 'rejected'],
        'order_shipped' => ['notif_order_status', 'shipped'],
        'order_done' => ['notif_order_status', 'done'],
        'payment_success' => ['notif_order_status', 'payment'],
        'payment_failed' => ['notif_order_status', 'payment'],
        'refund_refunded' => ['notif_order_status', 'refund'],
        'refund_rejected' => ['notif_order_status', 'refund'],
        'refund_needs_bank' => [null, 'refund'],
        'review_reminder' => ['notif_review_reminder', 'review'],
        'new_product' => ['notif_new_product', 'product'],
    ];

    public function __construct(public string $event, public array $payload = [])
    {
    }

    public function via(object $notifiable): array
    {
        if (! array_key_exists($this->event, self::EVENTS)) {
            return [];
        }

        $column = self::EVENTS[$this->event][0];

        if ($column === null) {
            return ['database'];
        }

        return $notifiable->notificationPreferenceOrDefault()->{$column} ? ['database'] : [];
    }

    public function toArray(object $notifiable): array
    {
        return ['event' => $this->event, 'icon' => self::EVENTS[$this->event][1]] + $this->payload;
    }

    /** What the browser gets. Text is built here, at read time, so it follows the customer's current language. */
    public static function present(DatabaseNotification $notification): array
    {
        $data = $notification->data;
        $event = (string) ($data['event'] ?? '');
        $code = (string) ($data['code'] ?? '');
        $reason = trim((string) ($data['reason'] ?? ''));

        [$title, $body] = match ($event) {
            'order_process' => [__('Your order is being prepared'), $code],
            'order_rejected' => [__('Your order was declined'), $reason !== '' ? $code . ' · ' . $reason : $code],
            'order_shipped' => [__('Your order is on its way'), $code],
            'order_done' => [__('Your order is complete'), $code],
            'payment_success' => [__('Payment received'), $code],
            'payment_failed' => [__('Payment was not completed'), $code],
            'refund_refunded' => [__('Your refund has been sent'), $code],
            'refund_rejected' => [__('Your refund request was declined'), $code],
            'refund_needs_bank' => [__('Add your bank account to get your refund'), $code],
            'review_reminder' => [__('How was your order?'), __('Leave a quick review for :code', ['code' => $code])],
            'new_product' => [__('New on the menu'), (string) ($data['name'] ?? '')],
            default => [__('Notification'), ''],
        };

        return [
            'id' => $notification->id,
            'event' => $event,
            'icon' => (string) ($data['icon'] ?? 'default'),
            'title' => $title,
            'body' => $body,
            'open_url' => route('notifications.open', $notification->id, false),
            'read' => $notification->read_at !== null,
            'at' => $notification->created_at?->timestamp ?? 0,
        ];
    }
}
