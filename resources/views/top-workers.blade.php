<x-layouts.app>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Most Active This Week</h1>

        <!-- Top 3 users -->
        <x-list-users.top-users :topUsers="$topUsers" />

        @php
            // Dummy data voor ALLE gebruikers (inclusief top 3)
            $activities = [
                [
                    'user' => ['name' => 'John Doe', 'avatar_url' => 'https://i.pravatar.cc/150?img=1'],
                    'description' => 'Completed task X for project Y',
                    'project_name' => 'Project Alpha',
                    'duration' => '4:35:25',
                    'is_active' => true,
                    'end_time' => null
                ],
                [
                    'user' => ['name' => 'Jane Smith', 'avatar_url' => 'https://i.pravatar.cc/150?img=2'],
                    'description' => 'Worked on task Y for project Z and did bug fixing',
                    'project_name' => 'Project Beta',
                    'duration' => '3:10:02',
                    'is_active' => false,
                    'end_time' => now()->subHours(2)
                ],
                [
                    'user' => ['name' => 'Michael Johnson', 'avatar_url' => 'https://i.pravatar.cc/150?img=3'],
                    'description' => 'Bug fixes for feature A and other improvements',
                    'project_name' => 'Project Gamma',
                    'duration' => '5:16:20',
                    'is_active' => true,
                    'end_time' => null
                ],
                [
                    'user' => ['name' => 'Emily Davis', 'avatar_url' => 'https://i.pravatar.cc/150?img=4'],
                    'description' => 'Refactored component X',
                    'project_name' => 'Project Delta',
                    'duration' => '2:45:10',
                    'is_active' => false,
                    'end_time' => now()->subMinutes(30)
                ],
            ];
        @endphp

        <!-- Activity Table: Toon ALLE gebruikers -->
        <x-list-users.activity-table :activities="$activities" />
    </div>
</x-layouts.app>