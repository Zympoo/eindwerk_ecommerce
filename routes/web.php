<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'login')->name('login');
    Route::livewire('/register', 'register')->name('register');
});

Route::livewire('/products', 'products.index')->name('home');
Route::livewire('/products/{product:slug}', 'products.show');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/products');
})->name('logout');