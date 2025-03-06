<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompleteRegistrationController extends Controller
{
    // Toont het formulier voor contracttype en Toggl ID
    public function showForm()
    {
        return view('components.layouts.auth.complete-registration');  // Pad is nu aangepast
    }

    // Bewaart de contracttype en Toggl ID na formulierinvoer
    public function saveDetails(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|integer',
            'toggl_user_id' => 'required|string',
        ]);

        $user = auth()->user();
        $user->contract_id = $request->contract_id;
        $user->toggl_user_id = $request->toggl_user_id;
        $user->save();

        // Redirect naar het dashboard na succesvolle registratie
        return redirect()->route('dashboard');
    }
}