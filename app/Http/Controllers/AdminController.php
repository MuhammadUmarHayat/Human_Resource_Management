<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Employee Statistics
        |--------------------------------------------------------------------------
        */

        $totalEmployees = Employee::count();

        $activeEmployees = Employee::where(
            'status',
            true
        )->count();


        /*
        |--------------------------------------------------------------------------
        | Organization Statistics
        |--------------------------------------------------------------------------
        */

        $totalDepartments = Department::count();

        $totalDesignations = Designation::count();


        /*
        |--------------------------------------------------------------------------
        | Today's Attendance
        |--------------------------------------------------------------------------
        */

        $today = now()->toDateString();

        $todayAttendance = Attendance::where(
            'attendance_date',
            $today
        );


        $presentToday = (clone $todayAttendance)
            ->where('status', 'Present')
            ->count();


        $absentToday = (clone $todayAttendance)
            ->where('status', 'Absent')
            ->count();


        $leaveToday = (clone $todayAttendance)
            ->where('status', 'Leave')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Leave Applications
        |--------------------------------------------------------------------------
        */

        $pendingLeaves = Leave::where(
            'status',
            'Pending'
        )->count();


        $approvedLeaves = Leave::where(
            'status',
            'Approved'
        )->count();


        $rejectedLeaves = Leave::where(
            'status',
            'Rejected'
        )->count();


        /*
        |--------------------------------------------------------------------------
        | Payroll Statistics
        |--------------------------------------------------------------------------
        */

        $currentMonth = now()->month;

        $currentYear = now()->year;


        $payrollQuery = Payroll::where(
            'month',
            $currentMonth
        )
        ->where(
            'year',
            $currentYear
        );


        $draftPayrolls = (clone $payrollQuery)
            ->where('status', 'Draft')
            ->count();


        $processedPayrolls = (clone $payrollQuery)
            ->where('status', 'Processed')
            ->count();


        $paidPayrolls = (clone $payrollQuery)
            ->where('status', 'Paid')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Dashboard Data
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Admin/Dashboard',
            [

                'statistics' => [

                    'totalEmployees' =>
                        $totalEmployees,

                    'activeEmployees' =>
                        $activeEmployees,

                    'totalDepartments' =>
                        $totalDepartments,

                    'totalDesignations' =>
                        $totalDesignations,

                    'presentToday' =>
                        $presentToday,

                    'absentToday' =>
                        $absentToday,

                    'leaveToday' =>
                        $leaveToday,

                    'pendingLeaves' =>
                        $pendingLeaves,

                    'approvedLeaves' =>
                        $approvedLeaves,

                    'rejectedLeaves' =>
                        $rejectedLeaves,

                    'draftPayrolls' =>
                        $draftPayrolls,

                    'processedPayrolls' =>
                        $processedPayrolls,

                    'paidPayrolls' =>
                        $paidPayrolls,

                ],

            ]
        );
    }
}