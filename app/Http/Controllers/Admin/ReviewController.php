<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReviewController extends Controller
{
    private const PER_PAGE = 10;

    private const SESSION_KEY = 'admin_review_deleted';

    public function __construct()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function index(Request $request): View|RedirectResponse
    {
        $rating = $request->get('rating', 'all');
        $search = trim((string) $request->get('search', ''));
        $sort = $request->get('sort', 'latest');

        $all = $this->reviews();
        $reviews = $all;

        $reviews = match ($rating) {
            '5' => $reviews->where('rating', 5),
            '4' => $reviews->where('rating', 4),
            '3' => $reviews->where('rating', '<=', 3),
            default => $reviews,
        };

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $reviews = $reviews->filter(fn (array $review) => str_contains(
                mb_strtolower($review['product'] . ' ' . $review['reviewer'] . ' ' . $review['text']),
                $needle
            ));
        }

        $reviews = match ($sort) {
            'oldest' => $reviews->sortBy('timestamp'),
            'rating_high' => $reviews->sortByDesc('rating'),
            'rating_low' => $reviews->sortBy('rating'),
            'product' => $reviews->sortBy('product', SORT_NATURAL | SORT_FLAG_CASE),
            default => $reviews->sortByDesc('timestamp'),
        };
        $reviews = $reviews->values();

        // After deleting the last row of a page, go to the new last page.
        $page = LengthAwarePaginator::resolveCurrentPage();
        $lastPage = max(1, (int) ceil($reviews->count() / self::PER_PAGE));
        if ($page > $lastPage) {
            $request->session()->reflash();

            return redirect()->route('admin.review', array_merge($request->query(), ['page' => $lastPage]));
        }

        $paginator = new LengthAwarePaginator(
            $reviews->forPage($page, self::PER_PAGE)->values(),
            $reviews->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.review-index', [
            'reviews' => $paginator,
            'totalReviews' => $all->count(),
            'averageRating' => $all->isEmpty() ? 0 : round($all->avg('rating'), 1),
            'rating' => $rating,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function destroy(int $review): RedirectResponse
    {
        $current = $this->reviews()->firstWhere('id', $review);

        abort_if($current === null, 404);

        $deleted = session(self::SESSION_KEY, []);
        $deleted[] = $review;
        session([self::SESSION_KEY => array_values(array_unique($deleted))]);

        return back()->with('status', "Review by {$current['reviewer']} for {$current['product']} has been deleted.");
    }

    /* ------------------------------------------------------------------
     | Temporary data layer.
     | Reviews are hardcoded until the reviews table exists. Deleted
     | reviews are remembered in the session so the flow can be
     | demonstrated. Replace reviews() and the session bookkeeping in
     | destroy() with Eloquent queries later.
     | Product name, price and photo come from the real menu_items table
     | when a product with the same name exists.
     ------------------------------------------------------------------ */

    private function reviews(): Collection
    {
        $deleted = session(self::SESSION_KEY, []);

        return $this->seed()->reject(fn (array $review) => in_array($review['id'], $deleted, true))->values();
    }

    private function seed(): Collection
    {
        // id, product, reviewer, email, member since, rating, review, date, order no.
        $rows = [
            [1, 'Choco Melt', 'Antonio', 'antonio@example.com', '2026-01', 5, 'Rich chocolate glaze and super soft dough. My whole family finished the box in ten minutes!', '2026-09-01', 1],
            [2, 'Vanilla Bean', 'Antonio', 'antonio@example.com', '2026-01', 5, 'The donuts were absolutely fresh and delicious! The glaze was perfect, not too sweet but just right. Delivery was also surprisingly fast. Highly recommended!', '2026-09-01', 1],
            [3, 'Original Glaze', 'Budi Santoso', 'budi.santoso@example.com', '2026-03', 4, 'Classic and simple, exactly how a glazed donut should taste. A little more glaze would make it perfect.', '2026-09-03', 3],
            [4, 'Zen Matcha', 'Dewi Lestari', 'dewi.lestari@example.com', '2026-02', 5, 'The matcha flavor is earthy and not bitter at all. Easily my favorite from the whole menu.', '2026-09-04', 4],
            [5, 'Salted Caramel', 'Dewi Lestari', 'dewi.lestari@example.com', '2026-02', 4, 'Sweet and salty in the right balance. It arrived slightly squished but tasted great.', '2026-09-04', 4],
            [6, 'Boston Cream', 'Siti Nurhaliza', 'siti.nurhaliza@example.com', '2026-04', 5, 'The custard filling is generous and creamy. I ordered again the very next day!', '2026-09-07', 7],
            [7, 'Cinnamon Sugar', 'Siti Nurhaliza', 'siti.nurhaliza@example.com', '2026-04', 5, 'Warm cinnamon smell the moment I opened the box. Perfect with my morning coffee.', '2026-09-07', 7],
            [8, 'Zen Matcha', 'Rizky Pratama', 'rizky.pratama@example.com', '2026-05', 3, 'Tasty, but the donut was a bit dry by the time it reached me. The flavor itself is good.', '2026-09-08', 8],
            [9, 'Strawberry Dream', 'Nadia Putri', 'nadia.putri@example.com', '2026-06', 5, 'So pretty and so tasty! The strawberry glaze tastes real, not artificial.', '2026-09-09', 9],
            [10, 'Midnight Glaze', 'Hendra Wijaya', 'hendra.wijaya@example.com', '2026-07', 5, 'Dark chocolate glaze with a nice bitter edge. Not too sweet, which I really appreciate.', '2026-09-10', 10],
            [11, 'Golden Cheese', 'Hendra Wijaya', 'hendra.wijaya@example.com', '2026-07', 4, 'Cheesy and savory-sweet, a unique combination. I would love an even bigger portion of cheese.', '2026-09-10', 10],
            [12, 'Honey Lemon', 'Antonio', 'antonio@example.com', '2026-01', 5, 'Bright and refreshing, the lemon cuts through the sweetness perfectly.', '2026-09-12', 11],
        ];

        $fallbackPrices = [
            'Original Glaze' => 12000, 'Choco Melt' => 15000, 'Strawberry Dream' => 15000, 'Vanilla Bean' => 14000,
            'Boston Cream' => 16000, 'Honey Lemon' => 14000, 'Cinnamon Sugar' => 13000, 'Zen Matcha' => 18000,
            'Salted Caramel' => 18000, 'Midnight Glaze' => 17500, 'Golden Cheese' => 19000,
        ];

        $products = MenuItem::query()
            ->whereIn('name', array_keys($fallbackPrices))
            ->get()
            ->keyBy('name');

        return collect($rows)->map(function (array $row) use ($products, $fallbackPrices) {
            [$id, $product, $reviewer, $email, $memberSince, $rating, $text, $date, $orderNo] = $row;

            $item = $products->get($product);
            $reviewedAt = Carbon::parse($date, 'Asia/Jakarta');

            return [
                'id' => $id,
                'product' => $product,
                'product_price' => 'Rp ' . number_format($item?->price ?? $fallbackPrices[$product], 0, ',', '.'),
                'product_image' => $item?->image_url ?? asset('assets/images/donat.png'),
                'reviewer' => $reviewer,
                'email' => $email,
                'member_since' => Carbon::parse($memberSince . '-01')->format('M Y'),
                'rating' => $rating,
                'text' => $text,
                'timestamp' => $reviewedAt->timestamp,
                'date_label' => $reviewedAt->format('d F Y'),
                'order_code' => '#DW-' . str_pad((string) $orderNo, 4, '0', STR_PAD_LEFT),
            ];
        });
    }
}