<div class="overflow-x-auto bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6">
    <table class="w-full table-auto">
        <thead>
            <tr class="border-b border-gray-300 dark:border-gray-700">
                <th class="py-4 px-4 text-left font-semibold text-gray-800 dark:text-white">Team Member</th>
                <th class="py-4 px-4 text-left font-semibold text-gray-800 dark:text-white">Description</th>
                <th class="py-4 px-4 text-left font-semibold text-gray-800 dark:text-white">Duration</th>
                <th class="py-4 px-4 text-left font-semibold text-gray-800 dark:text-white">End Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($activities as $activity)
                <x-list-users.activity-row :activity="$activity" />
            @endforeach
        </tbody>
    </table>
</div>