<div class="p-6 bg-white dark:bg-zinc-800 rounded-xl shadow-lg text-center">
    <!-- Iconen voor top 3 -->
    @if ($index == 0)
        <span class="text-4xl">🥇</span>
    @elseif ($index == 1)
        <span class="text-4xl">🥈</span>
    @elseif ($index == 2)
        <span class="text-4xl">🥉</span>
    @endif

    <h2 class="text-1xl font-bold mt-2 
        @if ($user->worked_hours < 38) text-orange-500 @else text-gray-900 dark:text-white @endif">
        {{ $user->name }}
    </h2>
    <p class="text-gray-600 dark:text-gray-300">{{ $user->worked_hours }} hours</p>
</div>
