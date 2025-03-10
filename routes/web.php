<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TopUserController;
use App\Http\Controllers\TogglAPIController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\CompleteRegistrationController;


Route::redirect('/', '/login')->name('home');
// Route::get('/', function () {
//     return view('welcome');
// })->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/complete-registration', [CompleteRegistrationController::class, 'showForm'])->name('complete.registration');
Route::post('/complete-registration', [CompleteRegistrationController::class, 'saveDetails']);

Route::get('/top-workers', [TopUserController::class, 'index'])->name('top-workers');

// Route::middleware(['auth'])->group(function () {
Route::middleware(['auth', 'ensure.email.domain'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

// Route to redirect to Google's OAuth page
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');

// Route to handle the callback from Google
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');


require __DIR__.'/auth.php';


Route::get('/GetActiveUsers', [TogglAPIController::class, 'getActiveUsers']);
Route::get('/GetActiveUsersIds', [TogglAPIController::class, 'getActiveUserIds']);

Route::get('/get-api-tokens', [TogglAPIController::class, 'getApiTokensForActiveUsers']);
Route::get('/current-entries', [TogglAPIController::class, 'showCurrentTimeEntries']);


Route::get('/getDetailedTimeEntries', [TogglAPIController::class, 'getDetailedTimeEntries']);

