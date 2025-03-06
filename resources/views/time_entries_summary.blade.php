<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Entries Summary</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        h1 { color: #333; }
        ul { list-style-type: none; padding: 0; }
        li { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .username { font-weight: bold; color: #2c3e50; }
        .hours { color: #27ae60; }
        .latest-entry { font-style: italic; color: #7f8c8d; }
    </style>
</head>
<body>
<h1>Time Entries Summary</h1>
<ul>
    @foreach($userSummaries as $summary)
        <li>
            <span class="username">{{ $summary['username'] }}</span>:
            <span class="hours">{{ $summary['total_hours'] }} hours</span>
            @if($summary['latest_entry'])
                <br>
                <span class="latest-entry">
                        Latest entry: {{ $summary['latest_entry']['description'] }}
                        (ended at {{ \Carbon\Carbon::parse($summary['latest_entry']['stop'])->format('Y-m-d H:i') }})
                    </span>
            @endif
        </li>
    @endforeach
</ul>
</body>
</html>
