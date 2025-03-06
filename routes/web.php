<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// })->name('home');

// Als de gebruiker ingelogd is dan wordt de gebruiker doorgestuurd naar de dashboard pagina
// Als de gebruiker niet ingelogd is dan wordt de gebruiker doorgestuurd naar de login pagina
Route::get('/', fn () => Auth::check() 
? redirect('dashboard') 
: Volt::render('auth.login'))
->name('login');

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
