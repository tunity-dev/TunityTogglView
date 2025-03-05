<?php

namespace App\Services;

use AllowDynamicProperties;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

#[AllowDynamicProperties] class TogglAPIService
{
    protected $apiToken;
    protected $workspaceId;

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
        $url ="https://api.track.toggl.com/api/v9/organizations/{$this->organizationId}/workspaces/{$this->workspaceId}/workspace_users";

        $response = Http::withBasicAuth($this->apiToken, 'api_token')->get($url);


        if ($response->failed()) {
            return [];
        }

        $users = collect($response->json());

/*        dd('Alle user inactive waarden:', $users->pluck('inactive')->unique());

        dd('Users API response:', $users);

*/
        $filteredUsers = $users->filter(fn($user) => $user['inactive'] === false);
       // return ["test"];

        //dd('Gefilterde actieve gebruikers:', $filteredUsers->values());

        // Filter alleen actieve gebruikers ("inactive": false)
        return $filteredUsers->values();
    }

}
