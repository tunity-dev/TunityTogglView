<div class="grid grid-cols-8">
    <div class="flex flex-col pr-2 w-16 mt-13">
        @for ($hour = 0; $hour <= 23; $hour++)
            <div class="h-12 flex items-center dark:border-neutral-700 text-gray-900 dark:text-white text-sm pl-2">
                {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00
            </div>
        @endfor
    </div>

    <div class="grid grid-cols-7 col-span-7 border-l border-neutral-300 dark:border-neutral-700">
        @php 
            $dates = ['3', '4', '5', '6', '7', '8', '9'];
        @endphp
        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $index => $day)
        <div class="flex flex-col items-center border-r border-neutral-300 dark:border-neutral-700 border-b pb-2">
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $dates[$index] }}</span>
                <span class="text-xs uppercase tracking-wider text-gray-600 dark:text-gray-400">{{ $day }}</span>
                <span id="loggedHours-{{ $day }}" class="text-xs text-gray-500 dark:text-gray-400 mt-1">0:00</span>
            </div>
        @endforeach

        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
            <div id="logContainer-{{ $day }}" class="relative flex flex-col bg-white dark:bg-zinc-800 h-[calc(24*3rem)] overflow-hidden border-r border-neutral-300 dark:border-neutral-700">
                @for ($hour = 0; $hour <= 23; $hour++)
                    <div class="h-12 border-b border-neutral-200 dark:border-neutral-700 relative"></div>
                @endfor
            </div>
        @endforeach
    </div>
</div>