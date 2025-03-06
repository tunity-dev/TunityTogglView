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

            <h2 class="text-1xl font-bold mt-2">
                {{ $user->name }}
            </h2>
            <p class="text-gray-600 dark:text-gray-300">{{ $user->worked_hours }} hours</p>
        </div>
    @endforeach
</div>