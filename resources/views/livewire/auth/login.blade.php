<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div class="flex items-center justify-center min-h-screen bg-black-background">
    <div class="w-full max-w-sm text-white p-8 rounded-lg shadow-lg">
        <x-auth-header title="TunityTogglView" description="Log in and get tracking!" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center text-sm mb-4" :status="session('status')" />

        <form wire:submit="login" class="flex flex-col gap-6 mt-10">
            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300">{{ __('Email') }}</label>
                <input 
                    wire:model="email" 
                    type="email" 
                    name="email" 
                    required 
                    autofocus 
                    autocomplete="email"
                    class="mt-2 block w-full px-4 py-3 bg-stone-850 border border-stone-600 rounded-md text-white focus:outline-none focus:ring-1 focus:white"
                    placeholder="Email Address"
                />
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300">{{ __('Password') }}</label>
                <input 
                    wire:model="password" 
                    type="password" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                    class="mt-2 block w-full px-4 py-3 bg-stone-850 border border-stone-600 rounded-md text-white focus:outline-none focus:ring-1 focus:white"
                    placeholder="Password"
                />
                @if (Route::has('password.request'))
                    <div class="mt-2 text-sm text-gray-400 text-right">
                        <flux:link :href="route('password.request')" wire:navigate class="text-[#e8592a] hover:underline">{{ __('Reset password?') }}</flux:link>
                    </div>
                @endif
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input wire:model="remember" type="checkbox" id="remember" class="h-4 w-4 rounded" />
                <label for="remember" class="ml-2 text-sm text-gray-300">{{ __('Remember me') }}</label>
            </div>

            <!-- Submit Button -->
            <div>
                <div class="flex flex-row gap-4 mt-5">
                    <!-- Inloggen via Google -->
                    {{-- <flux:button variant="primary" type="button" class="w-full rounded-md text-white bg-stone-700 border-1 hover:bg-amber-700" wire:click="loginWithGoogle">
                        {{ __('Log in with Google') }}
                    </flux:button> --}}

                    <!-- Inloggen via Google -->
                    <a href="{{ route('auth.google.redirect', ['action' => 'login']) }}" 
                        class="w-full rounded-md text-black bg-white hover:bg-orange-500 hover:text-white py-2 flex items-center justify-center text-center">
                        Google
                    </a>
                
                    <!-- Inloggen via Email -->
                    <flux:button variant="primary" type="submit" class="w-full rounded-md bg-orange-500 text-white hover:bg-amber-700">
                        {{ __('Log in') }}
                    </flux:button>
                </div>
                
            </div>
        </form>

        <div class="text-center text-sm text-gray-400 mt-4">
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up for an account') }}</flux:link>
        </div>
    </div>
</div>


