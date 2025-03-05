<?php

namespace App\Http\Controllers;

use App\Services\TogglAPIService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf("%02d:%02d", $hours, $minutes);
    }

}
