<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    @foreach ($weeklyHours->sortByDesc('total_hours')->take(3) as $index => $user)
        <div class="p-6 bg-white dark:bg-zinc-800 rounded-xl shadow-lg text-center">
            <span class="text-4xl">{{ $user['total_hours'] }}</span>

            <h2 class="text-1xl font-bold mt-2">
                {{ $user['username'] }}
            </h2>
        </div>
    @endforeach
</div>
