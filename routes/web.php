<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'login')->name('login');
    Route::livewire('/register', 'register')->name('register');
});

Route::prefix('products')
    ->group(function() {
        Route::livewire('/', 'products.index')->name('home');
        Route::livewire('/{product:slug}', 'products.show');
    });

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/products');
})->name('logout');