<?php

use App\Http\Controllers\TogglAPIController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});


require __DIR__.'/auth.php';


Route::get('/GetActiveUsers', [TogglAPIController::class, 'getActiveUsers']);
Route::get('/GetActiveUsersIds', [TogglAPIController::class, 'getActiveUserIds']);

Route::get('/get-api-tokens', [TogglAPIController::class, 'getApiTokensForActiveUsers']);
Route::get('/current-entries', [TogglAPIController::class, 'showCurrentTimeEntries']);


Route::get('/getDetailedTimeEntries', [TogglAPIController::class, 'getDetailedTimeEntries']);

