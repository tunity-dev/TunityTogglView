<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Time Entries</title>
    <meta http-equiv="refresh" content="60">
</head>
<body>
<h1>Current Time Entries</h1>

@foreach($currentTimeEntries as $userId => $data)
    <div>
        <h2>User: {{ $userNamesToggl[$userId] ?? 'Unknown User' }}</h2>

        @if($data['entry'])
            <h3>Current/Last Entry</h3>
            <p>Description: {{ $data['entry']['description'] }}</p>
            <p>Project ID: {{ $data['entry']['project_id'] ?? 'N/A' }}</p>
            <p>
                @if(isset($data['entry']['stop']))
                    Stop Time: {{ $data['entry']['stop'] }}
                @else
                    Start Time: {{ $data['entry']['start'] }}
                @endif
            </p>
            @if (isset($data['entry']['formatted_duration']))
                <p>Duration: <span id="duration-{{ $userId }}">{{ $data['entry']['formatted_duration'] }}</span></p>
            @endif
            @if($data['last_entry_ago'])
                <p>Last entry was {{ $data['last_entry_ago'] }}</p>
            @endif
        @else
            <p>No time entries found.</p>
        @endif
    </div>
@endforeach

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
</body>
</html>
