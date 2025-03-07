@props(['currentTimeEntries'])

<div class="overflow-x-auto bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6">
    <table class="w-full table-auto">
        <thead>
        <tr class="border-b border-gray-300 dark:border-gray-700">
            <th class="py-4 px-4 text-left font-semibold text-gray-800 dark:text-white">Team Member</th>
            <th class="py-4 px-4 text-left font-semibold text-gray-800 dark:text-white">Description</th>
            <th class="py-4 px-4 text-left font-semibold text-gray-800 dark:text-white">Duration</th>
            <th class="py-4 px-4 text-left font-semibold text-gray-800 dark:text-white">Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($currentTimeEntries as $userId => $activity)
            <tr class="border-b border-gray-200 dark:border-gray-700">
                <td class="py-2 px-4 flex items-center">
                    <img class="w-10 h-10 rounded-full mr-3" src="{{ asset('images/logo-tunity.png') }}" alt="User Avatar">
                    <span class="text-gray-800 dark:text-white">{{ $activity['username'] }}</span>
                </td>

                <td class="py-2 px-4">
                    <p class="text-gray-600 dark:text-gray-300">
                        {{ !empty($activity['entry']['description']) ? \Illuminate\Support\Str::limit($activity['entry']['description'], 30, '...') : 'No Description' }}
                    </p>
                </td>

                <td class="py-2 px-4 text-gray-600 dark:text-gray-300 min-w-28">
                    @if(isset($activity['entry']['running']) && $activity['entry']['running'] === true)
                        <span id="duration-{{ $userId }}">{{ $activity['entry']['formatted_duration'] }}</span>
                    @else
                        {{ $activity['entry']['formatted_duration'] ?? 'N/A' }}
                    @endif
                </td>

                <td class="py-2 px-4">
                    @if(isset($activity['entry']['running']) && $activity['entry']['running'] === true)
                        <span class="text-green-500">Running...</span>
                    @elseif(isset($activity['last_entry_ago']))
                        <span class="text-gray-500 dark:text-gray-400">{{ $activity['last_entry_ago'] }}</span>
                    @else
                        <span class="text-gray-500 dark:text-gray-400">Inactive</span>
                    @endif

                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<script>
    function updateDurations() {
        @foreach($currentTimeEntries as $userId => $data)
            @if($data['entry'] && isset($data['entry']['running']) && $data['entry']['running'])
        (function() {
            var userId = {{ $userId }};
            var startTime = new Date("{{ $data['entry']['start'] }}").getTime();

            function updateDuration() {
                var now = new Date().getTime();
                var durationInSeconds = Math.floor((now - startTime) / 1000);

                var hours = Math.floor(durationInSeconds / 3600);
                var minutes = Math.floor((durationInSeconds % 3600) / 60);
                var seconds = Math.floor(durationInSeconds % 60);

                var formattedDuration = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

                document.getElementById('duration-' + userId).innerText = formattedDuration;
            }

            updateDuration();
            setInterval(updateDuration, 1000);
        })();
        @endif
        @endforeach
    }

    window.onload = updateDurations;
</script>
