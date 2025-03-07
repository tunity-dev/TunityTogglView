<tr class="border-b border-gray-200 dark:border-gray-700">
    <!-- Team Member -->
    <td class="py-2 px-4 flex items-center">
        @if(isset($activity['entry']['user_id']) && isset($userNames[$activity['entry']['user_id']]))
            <img class="w-10 h-10 rounded-full mr-3" src="{{ asset('images/default-avatar.png') }}" alt="User Avatar"> {{-- Replace with actual avatar logic --}}
            <span class="text-gray-800 dark:text-white">{{ $userNames[$activity['entry']['user_id']] }}</span>
        @else
            <span class="text-gray-800 dark:text-white">Unknown User</span>
        @endif
    </td>

    <!-- Description -->
    <td class="py-2 px-4">
        <p class="text-gray-600 dark:text-gray-300">
            {{ \Illuminate\Support\Str::limit($activity['entry']['description'] ?? 'No Description', 30, '...') }}
        </p>
        {{-- Project Name is not available in the current entry, you may need to fetch project name separately or from last entry --}}
        {{-- <p class="text-sm text-gray-500 dark:text-gray-400">{{ $activity['project_name'] }}</p> --}}
    </td>

    <!-- Duration -->
    <td class="py-2 px-4 text-gray-600 dark:text-gray-300">
        @if(isset($activity['entry']['running']) && $activity['entry']['running'] === true)
            Running
        @else
            {{ $activity['entry']['formatted_duration'] ?? 'N/A' }}
        @endif
    </td>

    <!-- End Time -->
    <td class="py-2 px-4">
        @if(isset($activity['entry']['running']) && $activity['entry']['running'] === true)
            <span class="text-green-500">Running</span>
        @else
            <span class="text-gray-500 dark:text-gray-400">
                {{ $activity['last_entry_ago'] ?? 'N/A' }}
            </span>
        @endif
    </td>
</tr>
