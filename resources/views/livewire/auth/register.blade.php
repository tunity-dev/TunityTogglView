<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex items-center justify-center min-h-screen bg-black-background">
    <div class="w-full max-w-sm text-white p-8 rounded-lg shadow-lg">
        <x-auth-header title="TunityTogglView" description="Sign up and get tracking!" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form wire:submit="register" class="flex flex-col gap-6 mt-10">
            <!-- Name -->
            <flux:input
                wire:model="name"
                id="name"
                :label="__('Name')"
                type="text"
                name="name"
                required
                autofocus
                autocomplete="name"
                placeholder="Full name"
            />

            <!-- Email Address -->
            <flux:input
                wire:model="email"
                id="email"
                :label="__('Email address')"
                type="email"
                name="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                wire:model="password"
                id="password"
                :label="__('Password')"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Password"
            />

            <!-- Confirm Password -->
            <flux:input
                wire:model="password_confirmation"
                id="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Confirm password"
            />

            <div class="flex flex-row gap-4 pt-5">
                <flux:button variant="primary" type="submit" class="w-full rounded-md text-white bg-stone-700 border-1 hover:bg-amber-700">{{ __('Google') }}</flux:button>
                <flux:button variant="primary" type="submit" class="w-full rounded-md bg-orange-500 text-white hover:bg-amber-700">{{ __('Log in') }}</flux:button>
            </div>
        </form>

        <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400 mt-4">
            <flux:link :href="route('login')" wire:navigate>Log in to an existing account</flux:link>
        </div>
    </div>
</div>
