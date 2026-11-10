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
    /** Only a finished order can be reviewed. */
    public function canReview(Order $order): bool
    {
        return $order->status === Order::STATUS_DONE;
    }

    /**
     * Order lines the customer can still review: the order is done, the product still exists
     * and the line has no review yet.
     *
     * @return Collection<int, OrderItem>
     */
    public function reviewableItems(Order $order): Collection
    {
        if (! $this->canReview($order)) {
            return collect();
        }

        return $order->items
            ->filter(fn (OrderItem $item) => $item->menu_item_id !== null && $item->review === null)
            ->values();
    }

    /**
     * Saves the reviews the customer filled in. Lines left without a rating are skipped, as are
     * lines that are not part of this order or are already reviewed.
     *
     * @param  array<int|string, array{rating?: mixed, comment?: mixed}>  $entries  keyed by order_items.id
     * @return int number of reviews created
     */
    public function submit(User $user, Order $order, array $entries): int
    {
        abort_unless((int) $order->user_id === (int) $user->id, 404);
        abort_unless($this->canReview($order), 403);

        try {
            return DB::transaction(function () use ($user, $order, $entries) {
                // Lock the order row so a double click / double submit is handled one after the other.
                Order::query()->whereKey($order->id)->lockForUpdate()->first();

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

                return $created;
            });
        } catch (UniqueConstraintViolationException) {
            // Lost a race with another request that reviewed the same line: nothing more to save.
            return 0;
        }
    }

    /**
     * Rating summary of one product.
     *
     * @return array{count: int, average: float, breakdown: array<int, array{count: int, percent: int}>}
     */
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

    /** Shape used by the public product pages. */
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
