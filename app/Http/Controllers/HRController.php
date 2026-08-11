<?php
//hr.php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\Attendance;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HRController extends Controller
{
    public function index(): Response
    {
        $today = now()->toDateString();

        return Inertia::render(
            'HR/Dashboard',
            [

                'totalEmployees' =>
                    Employee::count(),

                'totalDepartments' =>
                    Department::count(),

                'totalDesignations' =>
                    Designation::count(),

                'pendingLeaves' =>
                    Leave::where(
                        'status',
                        'Pending'
                    )->count(),

                'payrollRecords' =>
                    Payroll::count(),

                'presentToday' =>
                    Attendance::where(
                        'attendance_date',
                        $today
                    )
                    ->where(
                        'status',
                        'Present'
                    )
                    ->count(),

            ]
        );
    }
}