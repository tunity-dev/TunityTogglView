<?php

namespace App\Http\Controllers;

use App\Services\TogglAPIService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;


class TogglAPIController extends Controller
{
    protected TogglAPIService $togglService;

    public function __construct(TogglAPIService $togglService)
    {
        $this->togglService = $togglService;
    }

    /**
     * Retourneer een lijst van actieve gebruikers met lopende time entries.
     * @throws ConnectionException
     */
    public function getActiveUsers(): JsonResponse
    {
        $activeUsers = $this->togglService->getActiveUsers();
        return response()->json($activeUsers);
    }
    public function getActiveUserIds(): JsonResponse
    {
        $activeUsersIds = $this->togglService->getActiveUserIds();
        return response()->json($activeUsersIds);
    }

    public function getApiTokensForActiveUsers()
{
    // Haal actieve gebruikers op
    $activeUserIds = $this->togglService->getActiveUserIds();

    // Haal API-tokens op voor deze gebruikers
    $apiTokens = $this->togglService->getApiTokensForActiveUsers($activeUserIds);

    return response()->json($apiTokens);
}

    public function getDetailedTimeEntries()
    {
        $detailedEntries = $this->togglService->getDetailedTimeEntries();

        $userSummaries = [];

        foreach ($detailedEntries as $userId => $userData) {
            $username = $userData['username'] ?? 'Unknown User';
            $timeEntries = $userData['entries'] ?? [];

            $totalSeconds = 0;
            $latestEntry = null;

            foreach ($timeEntries as $timeEntry) {
                $totalSeconds += $timeEntry['duration'] ?? 0;

                if (!$latestEntry || ($timeEntry['stop'] ?? '') > ($latestEntry['stop'] ?? '')) {
                    $latestEntry = $timeEntry;
                }
            }

            $userSummaries[] = [
                'username' => $username,
                'total_hours' => $this->formatDuration($totalSeconds),
                'total_hours_decimal' => round($totalSeconds / 3600, 2),
                'latest_entry' => $latestEntry ? [
                    'description' => $latestEntry['description'] ?? 'No description',
                    'stop' => $latestEntry['stop'] ?? 'Unknown time'
                ] : null
            ];
        }

        // Sort by total hours descending
        usort($userSummaries, function($a, $b) {
            return $b['total_hours_decimal'] <=> $a['total_hours_decimal'];
        });

        return view('time_entries_summary', ['userSummaries' => $userSummaries]);
    }

    public function showCurrentTimeEntries()
    {
        $currentTimeEntries = $this->togglService->getCurrentTimeEntriesForAllUsers();
        $activeUsers = $this->togglService->getActiveUsers();

        $userNames = collect($activeUsers)->pluck('name', 'id')->toArray();
        $userNamesToggl = collect($activeUsers)->pluck('name', 'toggl_user_id')->toArray();

        // Format de duration in de controller
        $formattedTimeEntries = [];
        foreach ($currentTimeEntries as $userId => $data) {
            $formattedEntry = $data['entry'];
            if ($formattedEntry && isset($formattedEntry['duration'])) {
                $formattedEntry['formatted_duration'] = $this->formatDuration($formattedEntry['duration']);
            }
            $formattedTimeEntries[$userId] = [
                'entry' => $formattedEntry,
                'last_entry_ago' => $data['last_entry_ago']
            ];
        }

        return view('activity-table', [
            'currentTimeEntries' => $formattedTimeEntries,
            'userNames' => $userNames,
            'userNamesToggl' => $userNamesToggl
        ]);
    }

    private function formatDuration($seconds)
    {
        $hours = floor(abs($seconds) / 3600);
        $minutes = floor((abs($seconds) % 3600) / 60);
        $seconds = abs($seconds) % 60; // Overgebleven seconden
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }

}
