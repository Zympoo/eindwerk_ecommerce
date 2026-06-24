<?php

use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'login')->name('login');
    Route::livewire('/register', 'register')->name('register');
});

Route::prefix('products')->group(function() {
    Route::livewire('/', 'products.index')->name('home');
    Route::livewire('/{product:slug}', 'products.show')->name('products.show');
});

Route::livewire('/cart', 'cart.overview')->name('cart');
Route::livewire('/checkout', 'checkout.index')->name('checkout');

Route::prefix('orders')->group(function () {
    Route::livewire('/', 'order.index')->name('orders.index');
    Route::livewire('/{order}', 'order.show')->name('orders.show');
});

Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
Route::post('/orders/{order}/pay', [CheckoutController::class, 'pay'])->name('orders.pay');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/products');
})->name('logout');