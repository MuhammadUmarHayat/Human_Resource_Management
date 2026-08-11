<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;




class AttendanceController extends Controller
{
    
#region index
    public function index(Request $request)
    {
        $attendances = Attendance::with('employee')

            ->when($request->search, function ($query) use ($request) {

                $query->whereHas(
                    'employee',
                    function ($q) use ($request) {

                        $q->where(
                            'first_name',
                            'like',
                            "%{$request->search}%"
                        )

                        ->orWhere(
                            'last_name',
                            'like',
                            "%{$request->search}%"
                        );

                    }
                );

            })

            ->latest()

            ->paginate(10)

            ->withQueryString();

        return Inertia::render(

            'Admin/Attendances/Index',

            [

                'attendances' => $attendances,

                'filters' => [

                    'search' => $request->search

                ]

            ]

        );
    }
#endregion

#region mark
public function mark()
{
    $employees = Employee::with([
        'department',
        'designation'
    ])
    ->where('status', true)
    ->orderBy('first_name')
    ->get();

    return Inertia::render(
        'Admin/Attendances/Mark',
        [
            'today' => Carbon::today()->format('Y-m-d'),
            'employees' => $employees
        ]
    );
}
#endregion

#region store
public function storeDaily(Request $request)
{
    $request->validate([

        'attendance_date' => 'required|date',

        'employees' => 'required|array',

        'employees.*.employee_id' => 'required|exists:employees,id',

        'employees.*.status' => 'required',

        'employees.*.check_in' => 'nullable',

        'employees.*.check_out' => 'nullable',

        'employees.*.remarks' => 'nullable'

    ]);

    DB::transaction(function () use ($request) {

        foreach ($request->employees as $row) {

            $hours = 0;

            if (
                !empty($row['check_in']) &&
                !empty($row['check_out'])
            ) {

                $checkIn = Carbon::parse($row['check_in']);

                $checkOut = Carbon::parse($row['check_out']);

                $hours = round(

                    $checkOut->diffInMinutes($checkIn) / 60,

                    2

                );

            }

            Attendance::updateOrCreate(

                [

                    'employee_id' => $row['employee_id'],

                    'attendance_date' => $request->attendance_date

                ],

                [

                    'check_in' => $row['check_in'],

                    'check_out' => $row['check_out'],

                    'working_hours' => $hours,

                    'status' => $row['status'],

                    'remarks' => $row['remarks']

                ]

            );

        }

    });

    return redirect()

        ->route('admin.attendances.mark')

        ->with(

            'success',

            'Attendance saved successfully.'

        );

}
#endregion

#region attendence register
public function register(Request $request)
{
    $month = (int) ($request->month ?? now()->month);

$year = (int) ($request->year ?? now()->year);

$totalDays = Carbon::create($year, $month, 1)->daysInMonth;

    $employees = Employee::with('department')
        ->orderBy('first_name')
        ->get();

    $attendance = Attendance::whereYear(
            'attendance_date',
            $year
        )
        ->whereMonth(
            'attendance_date',
            $month
        )
        ->get()
        ->groupBy('employee_id');

    return Inertia::render(
    'Admin/Attendances/Register',
    [
        'employees' => $employees,
        'attendance' => $attendance,
        'month' => $month,
        'year' => $year,
        'totalDays' => $totalDays,
    ]
);
}

#endregion




}