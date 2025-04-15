<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RegisterController extends Controller
{
    public function __invoke(Request $request)
    {
        // Validate the form inputs
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Create the user
        User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Redirect to login page with success message
        return redirect()->route('login')->with('success', 'Account created successfully. Please log in.');
    }
}
