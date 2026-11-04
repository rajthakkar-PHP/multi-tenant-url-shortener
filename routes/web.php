<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
    Route::get('/invitations/{token}', [InvitationController::class, 'accept'])
        ->where('token', '[a-f0-9]{64}')
        ->name('invitations.accept');
    Route::post('/invitations/{token}', [InvitationController::class, 'complete'])
        ->where('token', '[a-f0-9]{64}')
        ->middleware('throttle:10,1')
        ->name('invitations.complete');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::resource('companies', CompanyController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::get('/companies/{company}/invitations/create', [InvitationController::class, 'create'])
        ->name('companies.invitations.create');
    Route::post('/companies/{company}/invitations', [InvitationController::class, 'store'])
        ->name('companies.invitations.store');

    Route::resource('short-urls', ShortUrlController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

Route::get('/{shortCode}', [ShortUrlController::class, 'redirect'])
    ->where('shortCode', '[A-Za-z0-9]{8,16}')
    ->name('short-urls.redirect');
