<x-layouts.app>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Most Active This Week</h1>

        <!-- Top 3 users -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            @foreach ($topUsers->take(3) as $index => $user)
                <div class="p-6 bg-white dark:bg-zinc-800 rounded-xl shadow-lg text-center">
                    @if ($index == 0)
                        <span class="text-4xl">🥇</span>
                    @elseif ($index == 1)
                        <span class="text-4xl">🥈</span>
                    @elseif ($index == 2)
                        <span class="text-4xl">🥉</span>
                    @endif
                    
                    <h2 class="text-1xl font-bold text-gray-900 dark:text-white mt-2">{{ $user->name }}</h2>
                    <p class="text-gray-600 dark:text-gray-300">{{ $user->worked_hours }} hours</p>
                </div>
            @endforeach
        </div>

        {{-- Rest van de users --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Other active workers</h2>
            <ul class="divide-y divide-gray-300 dark:divide-gray-700">
                @foreach ($topUsers->skip(3) as $index => $user)
                    <li class="py-2 flex justify-between">
                        <span class="text-gray-800 dark:text-white">{{ $index + 1 }}. {{ $user->name }}</span>
                        <span class="text-gray-600 dark:text-gray-300">{{ $user->worked_hours }} hours</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-layouts.app>

{{-- <x-layouts.app>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Most Active This Week</h1>

        <x-list-users.top-users-list :topUsers="$topUsers" />

        <x-list-users.other-users-list :topUsers="$topUsers" />
    </div>
</x-layouts.app> --}}
