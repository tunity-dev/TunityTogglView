<?php

namespace App\Services;

use AllowDynamicProperties;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;
use PDOException;

#[AllowDynamicProperties] class TogglAPIService
{
    public function __construct()
    {
        $this->apiToken = env('TOGGL_API_KEY'); // API-token uit .env
        $this->workspaceId = env('TOGGL_WORKSPACE_ID'); // Workspace ID uit .env
        $this->organizationId = env('TOGGL_ORGANIZATION_ID');
    }

    /**
     * Haal alle actieve gebruikers op uit de Toggl workspace.
     * @throws ConnectionException
     */
    public function getActiveUsers()
    {
        $url = "https://api.track.toggl.com/api/v9/organizations/{$this->organizationId}/workspaces/{$this->workspaceId}/workspace_users";

        $response = Http::withBasicAuth($this->apiToken, 'api_token')->get($url);


        if ($response->failed()) {
            return [];
        }

        $users = collect($response->json());

        /*        dd('Alle user inactive waarden:', $users->pluck('inactive')->unique());

                dd('Users API response:', $users);

        */
        $filteredUsers = $users->filter(fn($user) => $user['inactive'] === false);

        // Filter alleen actieve gebruikers ("inactive": false)
        return $filteredUsers->values();
    }

    public function getActiveUserIds()
    {
        $url = "https://api.track.toggl.com/api/v9/organizations/{$this->organizationId}/workspaces/{$this->workspaceId}/workspace_users";

        $response = Http::withBasicAuth($this->apiToken, 'api_token')->get($url);

        if ($response->failed()) {
            return [];
        }

        $users = collect($response->json());

        $activeUserIds = [];
        foreach ($users as $user) {
            if ($user['inactive'] === false) {
                $activeUserIds[] = $user['user_id'];
            }
        }

        return $activeUserIds;
    }

    public function getApiTokensForActiveUsers(array $userIds)
    {
        $defaultToken = "39975c97734e9497678faa171737f280";
        $apiTokens = [];

        try {
            // Verbind met de SQLite-database
            $dbPath = database_path('database.sqlite');
            $pdo = new \PDO("sqlite:$dbPath");

            // Controleer of de tabel bestaat
            //$tableExists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetchColumn();

            //if ($tableExists) {
                // Maak een query om tokens op te halen voor de opgegeven user_ids
                $placeholders = implode(',', array_fill(0, count($userIds), '?'));
                $query = "SELECT toggl_user_id, api_token FROM users WHERE toggl_user_id IN ($placeholders)";
                $statement = $pdo->prepare($query);

                // Voer de query uit met de user_ids als parameters
                $statement->execute($userIds);

                // Haal de resultaten op
                while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                    $apiTokens[$row['toggl_user_id']] = $row['api_token'] ?: $defaultToken;
                }
           // }
        } catch (\PDOException $e) {
            // Log de fout
            Log::error("Database error: " . $e->getMessage());
        }

        // Vul ontbrekende toggl_user_ids aan met de default token
        foreach ($userIds as $userId) {
            if (!isset($apiTokens[$userId])) {
                $apiTokens[$userId] = $defaultToken;
            }
        }

        return $apiTokens;
    }

    /**
     * @throws ConnectionException
     */
    public function getDetailedTimeEntries()
    {
        $url = "https://api.track.toggl.com/reports/api/v3/workspace/{$this->workspaceId}/search/time_entries";

        $endDate = now()->toDateString(); // Vandaag
        $startDate = now()->subDay()->toDateString(); // Gisteren

        $requestBody = [
            "start_date" => $startDate,
            "end_date" => $endDate,
            "page_size" => 2000000  // Pas dit aan naar behoefte
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->apiToken . ':api_token'),
            'Content-Type' => 'application/json'
        ])->post($url, $requestBody);

        if ($response->failed()) {
            //dd('API request failed:', $response->status(), $response->body());
            return ['API request failed:', $response->status(), $response->body()];
        }

        $detailedEntries = collect($response->json());

        $timeEntriesPerUser = [];

        foreach ($detailedEntries as $entry) {
            $userId = $entry['user_id'];
            $username = $entry['username'];

            if (!isset($timeEntriesPerUser[$userId])) {
                $timeEntriesPerUser[$userId] = [
                    'username' => $username,
                    'entries' => []
                ];
            }

            foreach ($entry['time_entries'] as $timeEntry) {
                $timeEntriesPerUser[$userId]['entries'][] = [
                    'id' => $timeEntry['id'],
                    'description' => $entry['description'],
                    'project_id' => $entry['project_id'],
                    'billable' => $entry['billable'],
                    'start' => $timeEntry['start'],
                    'stop' => $timeEntry['stop'],
                    'duration' => $timeEntry['seconds'],
                    'at' => $timeEntry['at'],
                    'at_tz' => $timeEntry['at_tz']
                ];
            }
        }

        // Debug: Toon het resultaat
        //dd($timeEntriesPerUser);

        return $timeEntriesPerUser;
    }

    public function getCurrentTimeEntriesForAllUsers()
    {
        $activeUserIds = $this->getActiveUserIds();
        $apiTokens = $this->getApiTokensForActiveUsers($activeUserIds);
        $currentEntries = [];

        foreach ($apiTokens as $userId => $apiToken) {
            $currentEntry = $this->getCurrentTimeEntryForUser($userId, $apiToken);

            if ($currentEntry) {
                $currentEntries[$userId] = [
                    'entry' => $currentEntry,
                    'last_entry_ago' => null // Geen laatste entry ago nodig
                ];
            } else {
                // Haal de laatste time entry op en bereken de 'ago'
                $lastEntry = $this->getLastTimeEntryForUser($userId, $apiToken);

                if ($lastEntry) {
                    $lastEntryAgo = Carbon::parse($lastEntry['stop'] ?? $lastEntry['start'])->diffForHumans();

                    $currentEntries[$userId] = [
                        'entry' => $lastEntry,
                        'last_entry_ago' => $lastEntryAgo
                    ];
                } else {
                    // Geen entries gevonden
                    $currentEntries[$userId] = [
                        'entry' => null,
                        'last_entry_ago' => 'No entries found'
                    ];
                }
            }
        }

        return $currentEntries;
    }

    private function getCurrentTimeEntryForUser($userId, $apiToken)
    {
        $url = "https://api.track.toggl.com/api/v9/me/time_entries/current";

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($apiToken . ':api_token'),
            'Content-Type' => 'application/json'
        ])->get($url);

        if ($response->successful()) {
            $entry = $response->json();
            if ($entry) {
                // Parse de starttijd en zet om naar de juiste tijdzone (+01:00)
                $startTime = Carbon::parse($entry['start'])->timezone('Europe/Brussels');
                $now = Carbon::now('Europe/Brussels');
                $durationInSeconds = $now->diffInSeconds($startTime, false);

                return [
                    'user_id' => $userId,
                    'description' => $entry['description'] ?? 'No description',
                    'project_id' => $entry['project_id'] ?? null,
                    'start' => $startTime->toDateTimeString(),
                    'stop' => $entry['stop'] ?? null,
                    'duration' => $durationInSeconds, // Sla de duur op in seconden
                    'workspace_id' => $entry['workspace_id'] ?? null,
                    'running' => true // Voeg een "running" attribuut toe
                ];
            }
        }

        return null;
    }

    private function getLastTimeEntryForUser($userId, $apiToken)
    {
        $url = "https://api.track.toggl.com/reports/api/v3/workspace/{$this->workspaceId}/search/time_entries";

        $endDate = now()->toDateString();
        $startDate = now()->subDays(30)->toDateString(); // Kijk 30 dagen terug

        $requestBody = [
            "start_date" => $startDate,
            "end_date" => $endDate,
            "user_ids" => [$userId],
            "page_size" => 1
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($apiToken . ':api_token'),
            'Content-Type' => 'application/json'
        ])->post($url, $requestBody);

        if ($response->successful()) {
            $data = $response->json();
            if (!empty($data) && isset($data[0]['time_entries'][0])) {
                $entry = $data[0]['time_entries'][0];
                return [
                    'user_id' => $userId,
                    'description' => $entry['description'] ?? 'No description',
                    'project_id' => $entry['project_id'] ?? null,
                    'start' => $entry['start'] ?? null,
                    'stop' => $entry['stop'] ?? null,
                    'duration' => $entry['seconds'] ?? 0,
                    'workspace_id' => $this->workspaceId
                ];
            }
        }

        return null;
    }

}
