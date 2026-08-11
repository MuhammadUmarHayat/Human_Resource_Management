<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('Admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('HR')) {
            return redirect()->route('hr.dashboard');
        }

        if ($user->hasRole('Employee')) {
            return redirect()->route('employee.dashboard');
        }

        abort(403, 'No role assigned.');
    }
}