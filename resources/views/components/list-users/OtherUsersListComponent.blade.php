<div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-4">
    <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Other active workers</h2>
    <ul class="divide-y divide-gray-300 dark:divide-gray-700">
        @foreach ($topUsers->skip(3) as $index => $user)
            <li class="py-2 flex justify-between">
                <span class="text-gray-800 dark:text-white 
                    @if ($user->worked_hours < 38) text-orange-500 @endif">
                    {{ $index + 1 }}. {{ $user->name }}
                </span>
                <span class="text-gray-600 dark:text-gray-300">{{ $user->worked_hours }} hours</span>
            </li>
        @endforeach
    </ul>
</div>
