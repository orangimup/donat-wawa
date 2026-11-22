<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function index(Request $request): View|RedirectResponse
    {
        $rating = (string) $request->get('rating', 'all');
        $search = trim((string) $request->get('search', ''));
        $sort = (string) $request->get('sort', 'latest');

        // order_items gives the product name as it was at purchase time (seeded reviews have no order
        // line, so menu_items is the fallback), users gives the
        // reviewer name. Both are needed by the search box and the "product" sort.
        $query = Review::query()
            ->select('reviews.*')
            ->leftJoin('order_items', 'order_items.id', '=', 'reviews.order_item_id')
            ->leftJoin('menu_items', 'menu_items.id', '=', 'reviews.menu_item_id')
            ->join('users', 'users.id', '=', 'reviews.user_id')
            ->with(['user', 'order', 'orderItem.menuItem', 'menuItem']);

        match ($rating) {
            '5' => $query->where('reviews.rating', 5),
            '4' => $query->where('reviews.rating', 4),
            '3' => $query->where('reviews.rating', '<=', 3),
            default => null,
        };

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('order_items.name', 'like', $like)
                    ->orWhere('menu_items.name', 'like', $like)
                    ->orWhere('users.name', 'like', $like)
                    ->orWhere('reviews.comment', 'like', $like);
            });
        }

        match ($sort) {
            'oldest' => $query->orderBy('reviews.created_at')->orderBy('reviews.id'),
            'rating_high' => $query->orderByDesc('reviews.rating')->orderByDesc('reviews.id'),
            'rating_low' => $query->orderBy('reviews.rating')->orderByDesc('reviews.id'),
            'product' => $query->orderByRaw('COALESCE(order_items.name, menu_items.name)')->orderByDesc('reviews.id'),
            default => $query->orderByDesc('reviews.created_at')->orderByDesc('reviews.id'),
        };

        $paginator = $query->paginate(self::PER_PAGE)->withQueryString();

        // After deleting the last row of a page, go to the new last page.
        if ($paginator->isEmpty() && $paginator->currentPage() > 1) {
            $request->session()->reflash();

            return redirect()->route('admin.review', array_merge($request->query(), ['page' => $paginator->lastPage()]));
        }

        return view('admin.review-index', [
            'reviews' => $paginator->through(fn (Review $review) => $this->present($review)),
            'totalReviews' => Review::count(),
            'averageRating' => round((float) Review::avg('rating'), 1),
            'rating' => $rating,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function destroy(int $review): RedirectResponse
    {
        $current = Review::with(['user', 'orderItem', 'menuItem'])->findOrFail($review);

        $reviewer = $current->user?->name ?? 'a customer';
        $product = $current->orderItem?->name ?? $current->menuItem?->name ?? 'a product';

        $current->delete();

        return back()->with('status', "Review by {$reviewer} for {$product} has been deleted.");
    }

    private function present(Review $review): array
    {
        $line = $review->orderItem;
        $product = $review->menuItem ?? $line?->menuItem;
        $reviewedAt = $review->created_at?->clone()->timezone(Order::TIMEZONE);

        return [
            'id' => $review->id,
            'product' => $line?->name ?? $product?->name ?? '-',
            'product_price' => 'Rp ' . number_format((int) ($product?->price ?? $line?->price ?? 0), 0, ',', '.'),
            'product_image' => $product?->image_url ?? asset('assets/images/donat.png'),
            'reviewer' => $review->user?->name ?? '-',
            'email' => $review->user?->email ?? '-',
            'member_since' => $review->user?->created_at?->format('M Y') ?? '-',
            'rating' => $review->rating,
            'text' => $review->comment ?? '—',
            'timestamp' => $reviewedAt?->timestamp ?? 0,
            'date_label' => $reviewedAt?->format('d F Y') ?? '-',
            'order_code' => $review->order?->display_code ?? '-',
        ];
    }
}
