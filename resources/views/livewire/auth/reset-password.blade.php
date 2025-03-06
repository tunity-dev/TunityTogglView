<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PasswordReset) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="flex items-center justify-center min-h-screen bg-black-background">
    <div class="w-full max-w-sm text-white p-8 rounded-lg shadow-lg">
        <x-auth-header title="TunityTogglView" description="Please enter your new password below" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center text-sm mb-4" :status="session('status')" />

        <form wire:submit="resetPassword" class="flex flex-col gap-6 mt-10">
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
                    class="mt-2 block w-full px-4 py-3 bg-stone-850 border border-stone-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-orange"
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
                    autocomplete="new-password"
                    class="mt-2 bl
