<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class TopUserController extends Controller
{
    public function index()
    {
        $topUsers = User::orderBy('worked_hours', 'desc')->get();

        return view('top-workers', compact('topUsers'));
    }
}
