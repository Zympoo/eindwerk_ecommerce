<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Gasten-routes (Alleen toegankelijk als je NIET bent ingelogd)
Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'login')->name('login');
    Route::livewire('/register', 'register')->name('register');
});

// Uitlog-route
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->name('logout');