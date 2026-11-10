<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const PREVIEW_REVIEWS = 5;
    private const REVIEWS_PER_PAGE = 8;

    public function show(MenuItem $menu_item, ReviewService $reviews): View
    {
        $related = MenuItem::query()
            ->where('category', $menu_item->category)
            ->where('id', '!=', $menu_item->id)
            ->where('status', 'active')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $relatedItems = $related->map(fn (MenuItem $item) => [
            'slug' => $item->slug,
            'name' => $item->name,
            'description' => $item->description,
            'image' => $item->image_url,
            'price' => $item->price,
            'price_formatted' => $item->price_formatted,
            'badge' => $item->badge,
            'status' => $item->is_available ? 'available' : 'unavailable',
            'category' => $item->category,
        ]);

        $latestReviews = Review::query()
            ->with('user')
            ->where('menu_item_id', $menu_item->id)
            ->latest()
            ->latest('id')
            ->limit(self::PREVIEW_REVIEWS)
            ->get()
            ->map(fn (Review $review) => $reviews->present($review));

        return view('user.detail-product', [
            'product' => $menu_item,
            'related' => $relatedItems,
            'reviews' => $latestReviews,
            'reviewSummary' => $reviews->summary($menu_item),
        ]);
    }

    public function reviews(Request $request, MenuItem $menu_item, ReviewService $reviews): View
    {
        // ?rating=1..5 filters by stars; anything else means "all".
        $rating = $request->integer('rating');
        $rating = $rating >= Review::MIN_RATING && $rating <= Review::MAX_RATING ? $rating : null;

        $paginator = Review::query()
            ->with('user')
            ->where('menu_item_id', $menu_item->id)
            ->when($rating !== null, fn ($query) => $query->where('rating', $rating))
            ->latest()
            ->latest('id')
            ->paginate(self::REVIEWS_PER_PAGE)
            ->withQueryString()
            ->through(fn (Review $review) => $reviews->present($review));

        return view('user.product-reviews', [
            'product' => $menu_item,
            'reviews' => $paginator,
            'reviewSummary' => $reviews->summary($menu_item),
            'ratingFilter' => $rating,
        ]);
    }
}
