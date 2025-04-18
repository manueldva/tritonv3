<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User; // Import the User model

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        /*$request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));*/

        if ($user->isAdmin()) {
            // User is an admin, proceed with login
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard', absolute: false)); // Or wherever admins should go

        } elseif ($user->isActive()) {
            // User is not an admin, but is active, proceed with login
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard', absolute: false)); // Or wherever regular users should go

        } else {
            // User is neither admin nor active, log them out and show a message
            Auth::logout(); // Log out the user immediately

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirect back to login with an error message
            return redirect()->route('login')->withErrors([
                'email' => 'Your account is not active.', // Or a more specific message
            ]);
        }

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
