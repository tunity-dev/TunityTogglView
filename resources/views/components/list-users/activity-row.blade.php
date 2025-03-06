<tr class="border-b border-gray-200 dark:border-gray-700">
    <!-- Team Member -->
    <td class="py-2 px-4 flex items-center">
        <img class="w-10 h-10 rounded-full mr-3" src="{{ $activity['user']['avatar_url'] }}" alt="User Avatar">
        <span class="text-gray-800 dark:text-white">{{ $activity['user']['name'] }}</span>
    </td>

    <!-- Description -->
    <td class="py-2 px-4">
        <p class="text-gray-600 dark:text-gray-300">
            {{ \Illuminate\Support\Str::limit($activity['description'], 30, '...') }}
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $activity['project_name'] }}</p>
    </td>

    <!-- Duration -->
    <td class="py-2 px-4 text-gray-600 dark:text-gray-300">
        {{ $activity['duration'] }}
    </td>

    <!-- End Time -->
    <td class="py-2 px-4">
        @if ($activity['is_active'])
            <span class="text-green-500">Running</span>
        @else
            <span class="text-gray-500 dark:text-gray-400">
                @if ($activity['end_time'])
                    {{ \Carbon\Carbon::parse($activity['end_time'])->diffForHumans() }}
                @else
                    Never ended
                @endif
            </span>
        @endif
    </td>
</tr>