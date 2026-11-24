<?php

use Illuminate\Support\Facades\Route;

/*
======================================
LOGIN RAPI
======================================
*/
Route::get('/login', function () {
    return view('login');
})->name('login');
// ======================================
// RESET PASSWORD DENGAN OTP
// ======================================

Route::view('/forgot-password', 'auth.passwords.email')
    ->name('password.request');

Route::view('/verify-otp', 'auth.passwords.verify')
    ->name('password.verify');

Route::view('/reset-password', 'auth.passwords.reset')
    ->name('password.reset');
/*
======================================
ROUTE WEB RAPI
======================================

*/
Route::prefix('rapi')->group(function () {
  

    Route::view('/dashboard', 'content.rapi.dashboard')
        ->name('rapi.dashboard');

    Route::view('/calendar', 'content.rapi.calendar')
        ->name('rapi.calendar');

    Route::view('/events', 'content.rapi.events')
        ->name('rapi.events');

    Route::view('/rooms', 'content.rapi.rooms')
        ->name('rapi.rooms');

    Route::view('/complaints', 'content.rapi.complaints')
        ->name('rapi.complaints');

    Route::get('/users', function () {
        $roles = \App\Models\Role::orderBy('role_name')->get();

        return view('content.rapi.users', [
            'roles' => $roles,
        ]);
    })->name('rapi.users');
    Route::view('/reports/monthly', 'content.rapi.monthly-report')
    ->name('rapi.reports.monthly');
});
/*
KALENDER PUBLIC
*/
Route::view('/', 'content.rapi.calender-public')
    ->name('guest-calendar');

Route::view('/calendar', 'content.rapi.calender-public')
    ->name('guest-calendar');
