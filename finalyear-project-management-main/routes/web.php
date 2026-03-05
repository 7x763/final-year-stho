<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Livewire\ExternalDashboard;
use App\Livewire\ExternalLogin;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/external/{token}', ExternalDashboard::class)->name('external.dashboard');
Route::get('/external/login/{token}', ExternalLogin::class)->name('external.login');

Route::get('/lang/{locale}', function (string $locale) {
    if (! in_array($locale, ['en', 'vi'])) {
        abort(400);
    }
    session()->put('locale', $locale);
    cookie()->queue(cookie()->forever('filament_language_switch_locale', $locale));
    return redirect()->back();
})->name('lang.switch');

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
