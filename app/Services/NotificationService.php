<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use App\Notifications\AdminNotification;
use App\Notifications\CustomerNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;
 
class NotificationService
{ 
    public function orderProcess(Order $order): void
    {
        $this->toCustomer($order, 'order_process');
    }

    public function orderRejected(Order $order, ?string $reason = null): void
    {
        $this->toCustomer($order, 'order_rejected', filled($reason) ? ['reason' => $reason] : []);
    }

    public function orderShipped(Order $order): void
    {
        $this->toCustomer($order, 'order_shipped');
    }
    public function orderCompleted(Order $order, bool $byCustomer): void
    {
        if (! $byCustomer) {
            $this->toCustomer($order, 'order_done');
        }

        $this->toAdmins('order_completed', $this->orderPayload($order, true));
    }

    public function paymentSuccess(Order $order): void
    {
        $this->toCustomer($order, 'payment_success');
        $this->toAdmins('order_paid', $this->orderPayload($order, true));
    }

    public function paymentFailed(Order $order): void
    {
        $this->toCustomer($order, 'payment_failed');
    }
 
    public function refundRequested(Refund $refund): void
    {
        $this->safely(function () use ($refund) {
            $order = $refund->order ?? Order::where('id_pesanan', $refund->id_pesanan)->first();

            if ($order === null) {
                return;
            }

            $this->toAdmins('refund_requested', [
                'order_id' => $order->id,
                'code' => $order->display_code,
                'customer' => $order->customer_name,
                'url' => route('admin.refund.show', $refund->id, false),
            ]);
        });
    }

    public function refundFailed(Refund $refund): void
    {
        $this->safely(function () use ($refund) {
            $order = $refund->order ?? Order::where('id_pesanan', $refund->id_pesanan)->first();

            if ($order === null) {
                return;
            }

            $this->toAdmins('refund_failed', [
                'order_id' => $order->id,
                'code' => $order->display_code,
                'customer' => $order->customer_name,
                'url' => route('admin.refund.show', $refund->id, false),
            ]);
        });
    }

    public function refundRefunded(Refund $refund): void
    {
        $this->toCustomerFromRefund($refund, 'refund_refunded');
    }

    public function refundRejected(Refund $refund): void
    {
        $this->toCustomerFromRefund($refund, 'refund_rejected');
    }

    public function refundApproved(Refund $refund): void
    {
        $this->toCustomerFromRefund($refund, 'refund_approved');
    }

    public function refundPickedUp(Refund $refund): void
    {
        $this->toCustomerFromRefund($refund, 'refund_picked_up');
    }

    public function refundNeedsBank(Refund $refund): void
    {
        $this->toCustomerFromRefund($refund, 'refund_needs_bank', function (Order $order) {
            return route('settings.orders.refund.bank', $order->id, false);
        });
    }
 
    public function reviewCreated(Order $order, User $reviewer): void
    {
        $this->safely(function () use ($order, $reviewer) {
            $this->toAdmins('review_created', [
                'order_id' => $order->id,
                'code' => $order->display_code,
                'customer' => $reviewer->name,
                'url' => route('admin.review', [], false),
            ]);
        });
    }

    public function reviewReminder(Order $order): void
    {
        $this->toCustomer($order, 'review_reminder', [
            'url' => route('settings.orders.review', $order->id, false),
        ]);
    }

    /** Only customers whose "New Product Alerts" toggle is on are even looked at (it is off by default). */
    public function newProduct(MenuItem $item): void
    {
        $this->safely(function () use ($item) {
            if ($item->status !== 'active') {
                return;
            }

            $payload = [
                'name' => $item->name,
                'url' => route('product.show', $item->slug, false),
            ];

            User::query()
                ->where('role', 'user')
                ->where('status', 'active')
                ->whereHas('notificationPreference', fn ($q) => $q->where('notif_new_product', true))
                ->with('notificationPreference')
                ->chunkById(200, function ($users) use ($payload) {
                    Notification::send($users, new CustomerNotification('new_product', $payload));
                });
        });
    }
 
    private function orderPayload(Order $order, bool $forAdmin = false): array
    {
        return [
            'order_id' => $order->id,
            'code' => $order->display_code,
            'url' => $forAdmin
                ? route('admin.orders.show', $order->id, false)
                : route('settings.orders.show', $order->id, false),
        ] + ($forAdmin ? ['customer' => $order->customer_name] : []);
    }

    private function toCustomer(Order $order, string $event, array $extra = []): void
    {
        $this->safely(function () use ($order, $event, $extra) {
            $user = $order->user;

            if ($user === null) {
                return;
            }

            $user->notify(new CustomerNotification($event, $extra + $this->orderPayload($order)));
        });
    }

    private function toCustomerFromRefund(Refund $refund, string $event, ?callable $url = null): void
    {
        $this->safely(function () use ($refund, $event, $url) {
            $order = $refund->order ?? Order::where('id_pesanan', $refund->id_pesanan)->first();
            $user = $order?->user;

            if ($order === null || $user === null) {
                return;
            }

            $payload = $this->orderPayload($order);

            if ($url !== null) {
                $payload['url'] = $url($order);
            }

            $user->notify(new CustomerNotification($event, $payload));
        });
    }

    private function toAdmins(string $event, array $payload): void
    {
        $this->safely(function () use ($event, $payload) {
            $admins = User::query()->where('role', 'admin')->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new AdminNotification($event, $payload));
            }
        });
    }

    private function safely(callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            Log::warning('Notification could not be sent', ['error' => $e->getMessage()]);
        }
    }
}
