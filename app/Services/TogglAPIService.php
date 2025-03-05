<?php

namespace App\Services;

use AllowDynamicProperties;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\NoReturn;

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

}
