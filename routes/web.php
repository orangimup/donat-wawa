<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationSettingsController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\UserController;
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

    Route::get('/settings/languages', [LanguageController::class, 'show'])->name('settings.languages');
    Route::post('/settings/languages', [LanguageController::class, 'update'])->name('settings.languages.update');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/menu-items')->name('dashboard');

    Route::get('/menu-items', [MenuItemController::class, 'index'])->name('menu-items.index');
    Route::post('/menu-items', [MenuItemController::class, 'store'])->name('menu-items.store');
    Route::put('/menu-items/{menuItem}', [MenuItemController::class, 'update'])->name('menu-items.update');
    Route::delete('/menu-items/{menuItem}', [MenuItemController::class, 'destroy'])->name('menu-items.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('user');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('user.update');
});