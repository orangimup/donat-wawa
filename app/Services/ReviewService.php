<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function canReview(Order $order): bool
    {
        return in_array($order->status, [Order::STATUS_SHIPPED, Order::STATUS_DONE], true);
    }
    public function reviewableItems(Order $order): Collection
    {
        if (! $this->canReview($order)) {
            return collect();
        }

        return $order->items
            ->filter(fn (OrderItem $item) => $item->menu_item_id !== null && $item->review === null)
            ->values();
    }
    public function submit(User $user, Order $order, array $entries): int
    {
        abort_unless((int) $order->user_id === (int) $user->id, 404);
        abort_unless($this->canReview($order), 403);

        try {
            return DB::transaction(function () use ($user, $order, $entries) {
                $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();

                $items = $order->items()
                    ->whereNotNull('menu_item_id')
                    ->whereDoesntHave('review')
                    ->get()
                    ->keyBy('id');

                $created = 0;

                foreach ($entries as $itemId => $entry) {
                    $rating = $entry['rating'] ?? null;

                    if ($rating === null || $rating === '') {
                        continue;
                    }

                    $item = $items->get((int) $itemId);

                    if ($item === null) {
                        continue;
                    }

                    $comment = trim((string) ($entry['comment'] ?? ''));

                    Review::create([
                        'order_item_id' => $item->id,
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'menu_item_id' => $item->menu_item_id,
                        'rating' => (int) $rating,
                        'comment' => $comment !== '' ? $comment : null,
                    ]);

                    $created++;
                }

                $completedNow = false;

                if ($created > 0 && $locked?->status === Order::STATUS_SHIPPED) {
                    $locked->update([
                        'status' => Order::STATUS_DONE,
                        'completed_at' => now(),
                    ]);

                    $completedNow = true;
                }

                if ($created > 0) {
                    $notifications = app(NotificationService::class);
                    $notifications->reviewCreated($order, $user);

                    if ($completedNow) {
                        // Reviewing a shipped order also completes it, so the admin hears about that too.
                        $notifications->orderCompleted($order, byCustomer: true);
                    }
                }

                return $created;
            });
        } catch (UniqueConstraintViolationException) {
            return 0;
        }
    }
    public function summary(MenuItem $menuItem): array
    {
        $counts = Review::query()
            ->where('menu_item_id', $menuItem->id)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $count = (int) $counts->sum();
        $sum = $counts->reduce(fn (int $carry, $total, $rating) => $carry + ((int) $rating * (int) $total), 0);

        $breakdown = [];
        for ($star = Review::MAX_RATING; $star >= Review::MIN_RATING; $star--) {
            $starCount = (int) ($counts[$star] ?? 0);
            $breakdown[$star] = [
                'count' => $starCount,
                'percent' => $count > 0 ? (int) round($starCount / $count * 100) : 0,
            ];
        }

        return [
            'count' => $count,
            'average' => $count > 0 ? round($sum / $count, 1) : 0.0,
            'breakdown' => $breakdown,
        ];
    }
    public function present(Review $review): array
    {
        return [
            'initial' => $review->reviewer_initial,
            'name' => $review->reviewer_label,
            'time' => $review->created_at?->diffForHumans(),
            'rating' => $review->rating,
            'star_group' => $review->rating,
            'text' => $review->comment,
        ];
    }
}