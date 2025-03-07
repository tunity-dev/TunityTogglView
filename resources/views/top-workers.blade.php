<x-layouts.app>
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Most Active This Week</h1>

    <x-list-users.top-users :weeklyHours="$weeklyHours" />

    <x-list-users.activity-table
        :currentTimeEntries="$currentTimeEntries"
        :users="$users"
    />
</x-layouts.app>
