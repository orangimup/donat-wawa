<?php

namespace App\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notification;

/** What the admin bell shows. Admins have no preferences: they always get these. */
class AdminNotification extends Notification
{
    public const EVENTS = ['order_paid', 'order_completed', 'refund_requested', 'refund_failed', 'review_created'];

    public function __construct(public string $event, public array $payload = [])
    {
    }

    public function via(object $notifiable): array
    {
        return in_array($this->event, self::EVENTS, true) ? ['database'] : [];
    }

    public function toArray(object $notifiable): array
    {
        return ['event' => $this->event] + $this->payload;
    }

    public static function present(DatabaseNotification $notification): array
    {
        $data = $notification->data;
        $event = (string) ($data['event'] ?? '');
        $code = (string) ($data['code'] ?? '');
        $customer = (string) ($data['customer'] ?? '');

        [$title, $text] = match ($event) {
            'order_paid' => [__('New paid order'), __(':code from :customer is paid and waiting for your confirmation.', ['code' => $code, 'customer' => $customer])],
            'order_completed' => [__('Order received by customer'), __(':code from :customer is now completed.', ['code' => $code, 'customer' => $customer])],
            'refund_requested' => [__('New refund request'), __(':customer requested a refund for :code.', ['code' => $code, 'customer' => $customer])],
            'refund_failed' => [__('Automatic refund failed'), __('The refund for :code could not be completed. Open Refunds to retry it.', ['code' => $code])],
            'review_created' => [__('New review'), __(':customer reviewed :code.', ['code' => $code, 'customer' => $customer])],
            default => [__('Notification'), ''],
        };

        return [
            'key' => $notification->id,
            'id' => (int) ($data['order_id'] ?? 0),
            'type' => $event,
            'code' => $code,
            'customer' => $customer,
            'title' => $title,
            'text' => $text,
            'at' => $notification->created_at?->timestamp ?? 0,
            'url' => (string) ($data['url'] ?? ''),
        ];
    }
}
