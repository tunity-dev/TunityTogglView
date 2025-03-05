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
}
