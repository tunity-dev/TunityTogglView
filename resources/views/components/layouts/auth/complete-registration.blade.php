<?php
    function register()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        Auth::login($user);

        session()->flash('status', 'Je registratie is voltooid! Vul nu je contract type en Toggl ID in.');
        $this->redirect(route('/dashboard')); 
    }
?>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body>
        <div class="flex items-center justify-center min-h-screen bg-black-background">
            <div class="w-full max-w-sm text-white p-8 rounded-lg shadow-lg">
                <x-auth-header title="Complete your registration" description="Please fill in your contract type and toggl user ID." />
        
                <!-- Session Status -->
                <x-auth-session-status class="text-center" :status="session('status')" />
        
                <form action="{{ route('complete.registration') }}" method="POST" class="flex flex-col gap-6 mt-10">
                    @csrf
        
                    <!-- Contract ID -->
                    <div>
                        <label for="contract_id" class="block text-sm font-medium text-gray-300">{{ __('Contract ID') }}</label>
                        <input 
                            id="contract_id" 
                            type="number" 
                            name="contract_id" 
                            required 
                            class="mt-2 block w-full px-4 py-3 bg-stone-850 border border-stone-600 rounded-md text-white focus:outline-none focus:ring-1 focus:white"
                            placeholder="Contract ID"
                        />
                    </div>
        
                    <!-- Toggl User ID -->
                    <div>
                        <label for="toggl_user_id" class="block text-sm font-medium text-gray-300">{{ __('Toggl User ID') }}</label>
                        <input 
                            id="toggl_user_id" 
                            type="text" 
                            name="toggl_user_id" 
                            required 
                            class="mt-2 block w-full px-4 py-3 bg-stone-850 border border-stone-600 rounded-md text-white focus:outline-none focus:ring-1 focus:white"
                            placeholder="Toggl User ID"
                        />
                    </div>

                    <div class="flex flex-row gap-4 mt-5">
                        <flux:button type="submit" class="w-full rounded-md bg-orange-500 text-white hover:bg-amber-700">
                            {{ __('Complete registration') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </body>
