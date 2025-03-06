<?php
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Maak de gebruiker aan
        $user = User::create($validated);

        // Log de gebruiker in
        Auth::login($user);

        // Redirect naar de complete registration pagina
        session()->flash('status', 'Je registratie is voltooid! Vul nu je contract type en Toggl ID in.');
        $this->redirect(route('complete.registration'));  // Verzendt de gebruiker naar het formulier om contract_id en toggl_user_id in te vullen
    }
?>

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

            <!-- Submit Button -->
            <div class="flex items-center justify-end mt-5">
                <button type="submit" class="w-full rounded-md text-white bg-stone-700 hover:bg-amber-700">
                    {{ __('Complete Registration') }}
                </button>
            </div>
        </form>
    </div>
</div>
