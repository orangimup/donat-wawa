<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationSettingsController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\DeliveryZoneController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/menu', [MenuController::class, 'index'])->name('menu');
Route::get('/menu/{menu_item}', [ProductController::class, 'show'])->name('product.show');
Route::get('/menu/{menu_item}/reviews', [ProductController::class, 'reviews'])->name('product.reviews');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::get('/checkout/sync-basket', [CheckoutController::class, 'syncBasket'])->name('checkout.sync-basket');
Route::post('/checkout/create-snap-token', [CheckoutController::class, 'createSnapToken'])->name('checkout.snap-token');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::post('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::middleware('guest')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/settings/notifications', [NotificationSettingsController::class, 'show'])->name('settings.notifications');
    Route::post('/settings/notifications', [NotificationSettingsController::class, 'update'])->name('settings.notifications.update');

    Route::get('/settings/orders', [OrderHistoryController::class, 'index'])->name('settings.orders');
    Route::get('/settings/orders/{order}', [OrderHistoryController::class, 'show'])->whereNumber('order')->name('settings.orders.show');

    Route::get('/settings/languages', [LanguageController::class, 'show'])->name('settings.languages');
    Route::post('/settings/languages', [LanguageController::class, 'update'])->name('settings.languages.update');
});


Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/menu-items', [MenuItemController::class, 'index'])->name('menu-items.index');
    Route::post('/menu-items', [MenuItemController::class, 'store'])->name('menu-items.store');
    Route::put('/menu-items/{menuItem}', [MenuItemController::class, 'update'])->name('menu-items.update');
    Route::delete('/menu-items/{menuItem}', [MenuItemController::class, 'destroy'])->name('menu-items.destroy');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm'])->whereNumber('order')->name('orders.confirm');
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->whereNumber('order')->name('orders.status');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

    Route::get('/delivery-zones', [DeliveryZoneController::class, 'index'])->name('delivery-zone');
    Route::post('/delivery-zones', [DeliveryZoneController::class, 'store'])->name('delivery-zone.store');
    Route::put('/delivery-zones/{zone}', [DeliveryZoneController::class, 'update'])->whereNumber('zone')->name('delivery-zone.update');
    Route::delete('/delivery-zones/{zone}', [DeliveryZoneController::class, 'destroy'])->whereNumber('zone')->name('delivery-zone.destroy');

    Route::get('/refunds', [RefundController::class, 'index'])->name('refund');
    Route::get('/refunds/{refund}', [RefundController::class, 'show'])->whereNumber('refund')->name('refund.show');
    Route::post('/refunds/{refund}/finalize', [RefundController::class, 'finalize'])->whereNumber('refund')->name('refund.finalize');

    Route::get('/users', [UserController::class, 'index'])->name('user');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('user.update');

    Route::get('/reviews', [ReviewController::class, 'index'])->name('review');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->whereNumber('review')->name('review.destroy');
});