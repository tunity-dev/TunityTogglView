<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Time Entries</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        h1 { color: #333; }
        ul { list-style-type: none; padding: 0; }
        li { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .user-name { font-weight: bold; color: #2c3e50; }
        .description { color: #34495e; }
        .duration { color: #27ae60; }
    </style>
</head>
<body>
<h1>Current Time Entries</h1>
@if(count($currentEntries) > 0)
    <ul>
        @foreach($currentEntries as $entry)
            <li>
                <span class="user-name">{{ $entry['user_name'] }}</span><br>
                <span class="description">{{ $entry['description'] }}</span><br>
                Project: {{ $entry['project'] }}<br>
                Started: {{ $entry['start_time'] }}<br>
                <span class="duration">Duration: {{ $entry['duration'] }}</span>
            </li>
        @endforeach
    </ul>
@else
    <p>No active time entries found.</p>
@endif
</body>
</html>
