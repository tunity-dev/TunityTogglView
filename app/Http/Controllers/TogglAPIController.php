<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TogglAPIService;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\Response;

class TogglAPIController extends Controller
{
    protected TogglAPIService $togglService;

    public function __construct(TogglAPIService $togglService)
    {
        $this->togglService = $togglService;
    }

    public function showDashboard()
    {
        // Get data from Toggl API
        $weeklyHours = $this->getWeeklyHours();
        $currentTimeEntriesData = $this->showCurrentTimeEntries();
        $activeUserIds = $this->togglService->getActiveUserIds();

        // Haal gebruikers op uit de database
        $users = User::all();

        // Filter en bereid de currentTimeEntries voor met gebruikersinformatie uit de database
        $currentTimeEntries = collect($currentTimeEntriesData['currentTimeEntries'])
            ->filter(function ($entry, $togglUserId) use ($users) {
                return $users->contains('toggl_user_id', $togglUserId) || $togglUserId == '11760019';
            })
            ->map(function ($entry, $togglUserId) use ($users, $activeUserIds) {
                $user = $users->firstWhere('toggl_user_id', $togglUserId) ?? $users->firstWhere('toggl_user_id', '11760019');
                $entry['username'] = $user ? $user->name : 'Unknown User';
                $entry['user_id'] = $user ? $user->id : null;
                $entry['is_active'] = in_array($togglUserId, $activeUserIds);
                return $entry;
            });

        return view('top-workers', [
            'weeklyHours' => collect($weeklyHours),
            'currentTimeEntries' => $currentTimeEntries,
            'users' => $users
        ]);
    }


    private function formatDuration($seconds)
    {
        $hours = floor(abs($seconds) / 3600);
        $minutes = floor((abs($seconds) % 3600) / 60);
        $seconds = abs($seconds) % 60;
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }

    public function getWeeklyHours()
    {
        $weeklyHours = $this->togglService->getWeeklyHours();
        $activeUsers = $this->togglService->getActiveUsers();

        $userNames = collect($activeUsers)->pluck('name', 'toggl_user_id')->toArray();

        $formattedWeeklyHours = [];
        foreach ($weeklyHours as $userId => $data) {
            $formattedWeeklyHours[$userId] = [
                'username' => $data['username'] ?? 'Unknown User',
                'total_hours_decimal' => round($data['total_seconds'] / 3600, 2),
                'total_hours' => $this->formatDuration($data['total_seconds'] ?? 0)
            ];
        }

        return $formattedWeeklyHours;
    }


    public function showCurrentTimeEntries()
    {
        $currentTimeEntries = $this->togglService->getCurrentTimeEntriesForAllUsers();

        // Format de duration in de controller
        $formattedTimeEntries = [];
        foreach ($currentTimeEntries as $userId => $data) {
            $formattedEntry = $data['entry'];
            if ($formattedEntry) {
                if (isset($formattedEntry['duration'])) {
                    $formattedEntry['formatted_duration'] = $this->formatDuration($formattedEntry['duration']);
                }
                // Gebruik de nieuwe methode om de username op te halen
                $formattedEntry['username'] = $this->togglService->getUsernameById($userId) ?? 'Unknown User';
            }
            $formattedTimeEntries[$userId] = [
                'entry' => $formattedEntry,
                'last_entry_ago' => $data['last_entry_ago']
            ];
        }

        return [
            'currentTimeEntries' => $formattedTimeEntries
        ];
    }


}
