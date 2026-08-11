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

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(
    LoginRequest $request
): RedirectResponse
{
    $request->authenticate();

    $request->session()->regenerate();


    /*
    |--------------------------------------------------------------------------
    | Role Based Redirect
    |--------------------------------------------------------------------------
    */

    if ($request->user()->hasRole('Admin')) {

        return redirect()->intended(
            route('admin.dashboard')
        );

    }


    if ($request->user()->hasRole('HR')) {

        return redirect()->intended(
            route('hr.dashboard')
        );

    }


    if ($request->user()->hasRole('Employee')) 
        {

        return redirect()->intended(
            route('employee.dashboard')
        );

    }
// if ($request->user()->hasRole('Employee')) 
//         {

//         return "Employee is logged in";
//     }


    /*
    |--------------------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------------------
    */

    return redirect()->intended(
        route('dashboard')
    );
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
