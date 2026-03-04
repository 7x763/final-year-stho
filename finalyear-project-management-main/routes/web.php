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

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
