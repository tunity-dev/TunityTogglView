<?php

namespace App\Services;

use AllowDynamicProperties;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;
use PDOException;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise;
use Illuminate\Support\Arr;

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

    public function getUsernameById($userId)
    {
        $url = "https://api.track.toggl.com/api/v9/workspaces/{$this->workspaceId}/users";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->apiToken . ':api_token'),
                'Content-Type' => 'application/json'
            ])->get($url);

            if ($response->successful()) {
                $users = $response->json();
                foreach ($users as $user) {
                    if (isset($user['id']) && $user['id'] == $userId) {
                        return $user['fullname'] ?? $user['email'] ?? null;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error fetching username for user ID {$userId}: " . $e->getMessage());
        }

        return null;
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

    public function getWeeklyHours()
    {
        $url = "https://api.track.toggl.com/reports/api/v3/workspace/{$this->workspaceId}/search/time_entries";
        $startDate = now()->startOfWeek(CarbonInterface::MONDAY)->toDateString(); // Maandag van deze week
        $endDate = now()->endOfWeek(CarbonInterface::SUNDAY)->toDateString(); // Zondag van deze week

        $requestBody = [
            "start_date" => $startDate,
            "end_date" => $endDate,
            "page_size" => 2000000 // Pas dit aan naar behoefte
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
                    'total_seconds' => 0, // Initialiseer total_seconds
                ];
            }
            // Accumuleer de total_seconds voor de huidige user
            foreach ($entry['time_entries'] as $timeEntry) {
                $timeEntriesPerUser[$userId]['total_seconds'] += $timeEntry['seconds'];
            }
        }

        // Debug: Toon het resultaat
        return $timeEntriesPerUser;
    }

    public function getCurrentTimeEntriesForAllUsers()
    {
        $activeUserIds = $this->getActiveUserIds();
        $apiTokens = $this->getApiTokensForActiveUsers($activeUserIds);
        $currentEntries = [];

        $client = new Client();
        $concurrency = 3;

        $requests = function ($apiTokens) use ($client) {
            foreach ($apiTokens as $userId => $apiToken) {
                $url = "https://api.track.toggl.com/api/v9/me/time_entries/current";
                $request = new Request('GET', $url, [
                    'Authorization' => 'Basic ' . base64_encode($apiToken . ':api_token'),
                    'Content-Type' => 'application/json'
                ]);

                yield function () use ($client, $request, $userId, $apiToken) {
                    // Caching toevoegen
                    $cacheKey = "toggl_current_entry_{$userId}";

                    // Haal data uit de cache of fetch indien niet aanwezig
                    $cachedData = Cache::get($cacheKey);
                    if ($cachedData) {
                        return $cachedData;
                    }

                    return $client->sendAsync($request)
                        ->then(
                            function ($response) use ($apiToken, $client, $userId, $cacheKey) {
                                $data = json_decode($response->getBody(), true);

                                if ($data) {
                                    // Timezone handling
                                    try {
                                        $startTime = Carbon::parse($data['start'])->timezone('Europe/Brussels');
                                        $now = Carbon::now('Europe/Brussels');
                                        $durationInSeconds = $now->diffInSeconds($startTime, false);

                                        $result = [
                                            'user_id' => $userId,
                                            'entry' => [
                                                'description' => $data['description'] ?? 'No description',
                                                'project_id' => $data['project_id'] ?? null,
                                                'start' => $startTime->toDateTimeString(),
                                                'stop' => $startTime->toDateTimeString(),
                                                'duration' => $durationInSeconds,
                                                'workspace_id' => $data['workspace_id'] ?? null,
                                                'running' => true,
                                            ],
                                            'last_entry_ago' => null,
                                        ];

                                        // Cache de resultaten
                                        Cache::put($cacheKey, $result, 60);

                                        return $result;
                                    } catch (\Exception $e) {
                                        Log::error("Error parsing date for user ID {$userId}: " . $e->getMessage());
                                        return [
                                            'user_id' => $userId,
                                            'entry' => null,
                                            'last_entry_ago' => 'Error parsing date',
                                        ];
                                    }
                                } else {
                                    // Haal de laatste time entry op
                                    $url = "https://api.track.toggl.com/api/v9/me/time_entries";
                                    $request = new Request('GET', $url, [
                                        'Authorization' => 'Basic ' . base64_encode($apiToken . ':api_token'),
                                        'Content-Type' => 'application/json'
                                    ]);

                                    return $client->sendAsync($request)
                                        ->then(
                                            function ($response) use ($userId) {
                                                $time_entries = json_decode($response->getBody(), true);

                                                if ($time_entries && is_array($time_entries) && count($time_entries) > 0) {
                                                    $firstEntry = $time_entries[0];

                                                    try {
                                                        $lastEntryAgo = Carbon::parse($firstEntry['stop'] ?? $firstEntry['start'])->diffForHumans();
                                                        return [
                                                            'user_id' => $userId,
                                                            'entry' => [
                                                                'description' => $firstEntry['description'] ?? 'No description',
                                                                'project_id' => $firstEntry['project_id'] ?? null,
                                                                'start' => $firstEntry['start'] ?? null,
                                                                'stop' => $firstEntry['stop'] ?? null,
                                                                'duration' => $firstEntry['duration'] ?? 0,
                                                                'workspace_id' => $firstEntry['workspace_id'] ?? null,
                                                            ],
                                                            'last_entry_ago' => $lastEntryAgo,
                                                        ];
                                                    } catch (\Exception $e) {
                                                        Log::error("Error parsing date for last entry user ID {$userId}: " . $e->getMessage());
                                                        return [
                                                            'user_id' => $userId,
                                                            'entry' => null,
                                                            'last_entry_ago' => 'Error parsing date',
                                                        ];
                                                    }
                                                } else {
                                                    return [
                                                        'user_id' => $userId,
                                                        'entry' => null,
                                                        'last_entry_ago' => 'No entries found',
                                                    ];
                                                }
                                            },
                                            function ($exception) use ($userId) {
                                                Log::error("Error fetching last time entry for user ID {$userId}: " . $exception->getMessage());
                                                return [
                                                    'user_id' => $userId,
                                                    'entry' => null,
                                                    'last_entry_ago' => 'Error fetching data',
                                                ];
                                            }
                                        );
                                }
                            },
                            function ($exception) use ($userId) {
                                Log::error("Error fetching time entry for user ID {$userId}: " . $exception->getMessage());
                                return [
                                    'user_id' => $userId,
                                    'entry' => null,
                                    'last_entry_ago' => 'Error fetching data',
                                ];
                            }
                        );
                };
            }
        };

        $pool = new Pool($client, $requests($apiTokens), [
            'concurrency' => $concurrency,
            'fulfilled' => function ($response, $index) use (&$currentEntries, $apiTokens) {
                $userId = array_keys($apiTokens)[$index];
                $currentEntries[$userId] = $response;
            },
            'rejected' => function ($reason, $index) use (&$currentEntries, $apiTokens) {
                $userId = array_keys($apiTokens)[$index];
                Log::error("Request {$index} failed: " . $reason);
                $currentEntries[$userId] = [
                    'entry' => null,
                    'last_entry_ago' => 'Request failed',
                ];
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        // Haal gebruikersnamen op uit de database
        $userIds = array_keys($currentEntries);
        $users = User::whereIn('toggl_user_id', $userIds)
            ->orderBy('name') // Sorteer op naam
            ->get()
            ->keyBy('toggl_user_id');

        // Sorteer de resultaten op basis van de gebruikersnaam
        uksort($currentEntries, function ($a, $b) use ($users) {
            $nameA = $users[$a]->name ?? '';
            $nameB = $users[$b]->name ?? '';
            return strcasecmp($nameA, $nameB);
        });

        return $currentEntries;
    }
}
