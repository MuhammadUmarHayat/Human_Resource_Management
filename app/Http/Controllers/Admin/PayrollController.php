<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Attendance;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Display a listing of payroll records.
     */

#region index
    public function index(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | Month & Year
    |--------------------------------------------------------------------------
    */

    $month = (int) (
        $request->month
        ?? now()->month
    );

    $year = (int) (
        $request->year
        ?? now()->year
    );


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    $search = $request->search;

    $status = $request->status;

    $employeeId = $request->employee_id;


    /*
    |--------------------------------------------------------------------------
    | Payroll Listing
    |--------------------------------------------------------------------------
    */

    $payrolls = Payroll::with([
        'employee.department',
        'employee.designation',
    ])

        ->where(
            'month',
            $month
        )

        ->where(
            'year',
            $year
        )


        /*
        |--------------------------------------------------------------------------
        | Employee Search
        |--------------------------------------------------------------------------
        */

        ->when(
            $search,
            function ($query) use ($search) {

                $query->whereHas(
                    'employee',
                    function ($employeeQuery) use ($search) {

                        $employeeQuery
                            ->where(
                                'first_name',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhere(
                                'last_name',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhere(
                                'employee_code',
                                'like',
                                "%{$search}%"
                            );

                    }
                );

            }
        )


        /*
        |--------------------------------------------------------------------------
        | Employee Filter
        |--------------------------------------------------------------------------
        */

        ->when(
            $employeeId,
            function ($query) use ($employeeId) {

                $query->where(
                    'employee_id',
                    $employeeId
                );

            }
        )


        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        ->when(
            $status,
            function ($query) use ($status) {

                $query->where(
                    'status',
                    $status
                );

            }
        )


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        ->latest()

        ->paginate(10)

        ->withQueryString();


    /*
    |--------------------------------------------------------------------------
    | Payroll Summary
    |--------------------------------------------------------------------------
    |
    | Summary is based on selected Month & Year.
    |
    */

    $summaryQuery = Payroll::where(
        'month',
        $month
    )
    ->where(
        'year',
        $year
    );


    $payrollSummary = [

        'total' =>
            (clone $summaryQuery)
                ->count(),


        'draft' =>
            (clone $summaryQuery)
                ->where(
                    'status',
                    'Draft'
                )
                ->count(),


        'processed' =>
            (clone $summaryQuery)
                ->where(
                    'status',
                    'Processed'
                )
                ->count(),


        'paid' =>
            (clone $summaryQuery)
                ->where(
                    'status',
                    'Paid'
                )
                ->count(),


        'gross_salary' =>
            (clone $summaryQuery)
                ->sum('gross_salary'),


        'net_salary' =>
            (clone $summaryQuery)
                ->sum('net_salary'),

    ];


    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */

    $employees = Employee::orderBy(
        'first_name'
    )->get();


    /*
    |--------------------------------------------------------------------------
    | Inertia Response
    |--------------------------------------------------------------------------
    */

    return Inertia::render(
        'Admin/Payroll/Index',
        [

            'payrolls' =>
                $payrolls,


            'employees' =>
                $employees,


            'month' =>
                $month,


            'year' =>
                $year,


            'search' =>
                $search,


            'status' =>
                $status,


            'employeeId' =>
                $employeeId,


            'payrollSummary' =>
                $payrollSummary,

        ]
    );
}
#endregion index
    /**
     * Show the form for creating a new payroll.
     */
    public function create()
{
    $employees = Employee::orderBy('first_name')
        ->get();

    return Inertia::render(
        'Admin/Payroll/Create',
        [
            'employees' => $employees,
        ]
    );
}

#region store method
    /**
     * Store a newly created payroll.
     */
  public function store(Request $request)
{
    $validated = $request->validate([

        'employee_id' =>
            'required|exists:employees,id',

        'month' =>
            'required|integer|min:1|max:12',

        'year' =>
            'required|integer|min:2000|max:2100',

        'basic_salary' =>
            'required|numeric|min:0',

        'allowances' =>
            'nullable|numeric|min:0',

        'overtime' =>
            'nullable|numeric|min:0',

        'bonuses' =>
            'nullable|numeric|min:0',

        'deductions' =>
            'nullable|numeric|min:0',

        'remarks' =>
            'nullable|string|max:1000',

    ]);


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Payroll
    |--------------------------------------------------------------------------
    */

    $exists = Payroll::where(
        'employee_id',
        $validated['employee_id']
    )
    ->where(
        'month',
        $validated['month']
    )
    ->where(
        'year',
        $validated['year']
    )
    ->exists();


    if ($exists) {

        return back()
            ->withErrors([
                'employee_id' =>
                    'Payroll already exists for this employee and month.'
            ])
            ->withInput();

    }


    /*
    |--------------------------------------------------------------------------
    | Get Attendance Summary
    |--------------------------------------------------------------------------
    */

    $attendanceSummary = $this->getAttendanceSummary(
        $validated['employee_id'],
        $validated['month'],
        $validated['year']
    );


    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    */

    $allowances =
        $validated['allowances'] ?? 0;

    $overtime =
        $validated['overtime'] ?? 0;

    $bonuses =
        $validated['bonuses'] ?? 0;

    $deductions =
        $validated['deductions'] ?? 0;


    /*
    |--------------------------------------------------------------------------
    | Calculate Gross Salary
    |--------------------------------------------------------------------------
    */

    $grossSalary =
        $validated['basic_salary']
        + $allowances
        + $overtime
        + $bonuses;


    /*
    |--------------------------------------------------------------------------
    | Calculate Net Salary
    |--------------------------------------------------------------------------
    */

    $netSalary =
        $grossSalary
        - $deductions;


    /*
    |--------------------------------------------------------------------------
    | Create Payroll
    |--------------------------------------------------------------------------
    */

    Payroll::create([

        'employee_id' =>
            $validated['employee_id'],

        'month' =>
            $validated['month'],

        'year' =>
            $validated['year'],

        'basic_salary' =>
            $validated['basic_salary'],

        'allowances' =>
            $allowances,

        'overtime' =>
            $overtime,

        'bonuses' =>
            $bonuses,

        'deductions' =>
            $deductions,

        'gross_salary' =>
            $grossSalary,

        'net_salary' =>
            $netSalary,


        /*
        |--------------------------------------------------------------------------
        | Automatic Attendance Data
        |--------------------------------------------------------------------------
        */

        'working_days' =>
            $attendanceSummary['working_days'],

        'present_days' =>
            $attendanceSummary['present_days'],

        'leave_days' =>
            $attendanceSummary['leave_days'],

        'absent_days' =>
            $attendanceSummary['absent_days'],


        /*
        |--------------------------------------------------------------------------
        | Payroll Status
        |--------------------------------------------------------------------------
        */

        'status' =>
            'Draft',

        'remarks' =>
            $validated['remarks'] ?? null,

    ]);


    return redirect()
        ->route('admin.payroll.index')
        ->with(
            'success',
            'Payroll generated successfully.'
        );
}
#endregion store
    /**
     * Display the specified payroll.
     */
   public function show(string $id)
{
    $payroll = Payroll::with([
        'employee.department',
        'employee.designation',
    ])->findOrFail($id);



    return Inertia::render(
        'Admin/Payroll/Show',
        [
            'payroll' => $payroll,
        ]
    );
}


    /**
     * Show the form for editing payroll.
     */
   public function edit(string $id)
{
    $payroll = Payroll::with([
        'employee.department',
        'employee.designation',
    ])->findOrFail($id);


    /*
    |--------------------------------------------------------------------------
    | Only Draft Payroll Can Be Edited
    |--------------------------------------------------------------------------
    */

    if ($payroll->status !== 'Draft') {

        return redirect()
            ->route(
                'admin.payroll.show',
                $payroll->id
            )
            ->withErrors([
                'payroll' =>
                    'Only Draft payroll can be edited.'
            ]);

    }


    return Inertia::render(
        'Admin/Payroll/Edit',
        [
            'payroll' => $payroll,
        ]
    );
}


    /**
     * Update payroll.
     */
 public function update(
    Request $request,
    string $id
) {
    $payroll = Payroll::findOrFail($id);


    /*
    |--------------------------------------------------------------------------
    | Only Draft Payroll Can Be Updated
    |--------------------------------------------------------------------------
    */

    if ($payroll->status !== 'Draft') {

        return redirect()
            ->route(
                'admin.payroll.show',
                $payroll->id
            )
            ->withErrors([
                'payroll' =>
                    'Only Draft payroll can be updated.'
            ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Request
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([

        'basic_salary' =>
            'required|numeric|min:0',

        'allowances' =>
            'nullable|numeric|min:0',

        'overtime' =>
            'nullable|numeric|min:0',

        'bonuses' =>
            'nullable|numeric|min:0',

        'deductions' =>
            'nullable|numeric|min:0',

        'working_days' =>
            'required|integer|min:0',

        'present_days' =>
            'required|integer|min:0',

        'leave_days' =>
            'required|integer|min:0',

        'absent_days' =>
            'required|integer|min:0',

        'remarks' =>
            'nullable|string|max:1000',

    ]);


    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    */

    $allowances =
        $validated['allowances'] ?? 0;

    $overtime =
        $validated['overtime'] ?? 0;

    $bonuses =
        $validated['bonuses'] ?? 0;

    $deductions =
        $validated['deductions'] ?? 0;


    /*
    |--------------------------------------------------------------------------
    | Calculate Gross Salary
    |--------------------------------------------------------------------------
    */

    $grossSalary =
        $validated['basic_salary']
        + $allowances
        + $overtime
        + $bonuses;


    /*
    |--------------------------------------------------------------------------
    | Calculate Net Salary
    |--------------------------------------------------------------------------
    */

    $netSalary =
        $grossSalary
        - $deductions;


    /*
    |--------------------------------------------------------------------------
    | Update Payroll
    |--------------------------------------------------------------------------
    */

    $payroll->update([

        'basic_salary' =>
            $validated['basic_salary'],

        'allowances' =>
            $allowances,

        'overtime' =>
            $overtime,

        'bonuses' =>
            $bonuses,

        'deductions' =>
            $deductions,

        'gross_salary' =>
            $grossSalary,

        'net_salary' =>
            $netSalary,

        'working_days' =>
            $validated['working_days'],

        'present_days' =>
            $validated['present_days'],

        'leave_days' =>
            $validated['leave_days'],

        'absent_days' =>
            $validated['absent_days'],

        'remarks' =>
            $validated['remarks'] ?? null,

    ]);


    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route(
            'admin.payroll.show',
            $payroll->id
        )
        ->with(
            'success',
            'Payroll updated successfully.'
        );
}
    /**
     * Remove payroll.
     */
    public function destroy(string $id)
{
    $payroll = Payroll::findOrFail($id);


    /*
    |--------------------------------------------------------------------------
    | Only Draft Payroll Can Be Deleted
    |--------------------------------------------------------------------------
    */

    if ($payroll->status !== 'Draft') {

        return back()
            ->withErrors([
                'payroll' =>
                    'Only Draft payroll can be deleted.'
            ]);

    }


    $payroll->delete();


    return redirect()
        ->route('admin.payroll.index')
        ->with(
            'success',
            'Payroll deleted successfully.'
        );
}


    #region attendence summary

private function getAttendanceSummary(
    int $employeeId,
    int $month,
    int $year
) {
    $startDate = Carbon::create(
        $year,
        $month,
        1
    )->startOfMonth();


    $endDate = $startDate
        ->copy()
        ->endOfMonth();


    /*
    |--------------------------------------------------------------------------
    | Total Days In Month
    |--------------------------------------------------------------------------
    */

    $workingDays = $startDate->daysInMonth;


    /*
    |--------------------------------------------------------------------------
    | Get Employee Attendance
    |--------------------------------------------------------------------------
    */

    $attendance = Attendance::where(
        'employee_id',
        $employeeId
    )
    ->whereBetween(
        'attendance_date',
        [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ]
    )
    ->get();


    /*
    |--------------------------------------------------------------------------
    | Calculate Attendance
    |--------------------------------------------------------------------------
    */

    $presentDays = $attendance
        ->where('status', 'Present')
        ->count();


    $leaveDays = $attendance
        ->where('status', 'Leave')
        ->count();


    $absentDays = $attendance
        ->where('status', 'Absent')
        ->count();


    /*
    |--------------------------------------------------------------------------
    | Return Summary
    |--------------------------------------------------------------------------
    */

    return [

        'working_days' =>
            $workingDays,

        'present_days' =>
            $presentDays,

        'leave_days' =>
            $leaveDays,

        'absent_days' =>
            $absentDays,

    ];
}
    #endregion attendence summary
    #region process
    public function process(string $id)
{
    $payroll = Payroll::findOrFail($id);


    if ($payroll->status !== 'Draft') {

        return back()
            ->withErrors([
                'payroll' =>
                    'Only Draft payroll can be processed.'
            ]);

    }


    $payroll->update([

        'status' => 'Processed',

    ]);


    return back()
        ->with(
            'success',
            'Payroll processed successfully.'
        );
}
    #endregion process

    #region paid
    public function markAsPaid(string $id)
{
    $payroll = Payroll::findOrFail($id);


    if ($payroll->status !== 'Processed') {

        return back()
            ->withErrors([
                'payroll' =>
                    'Only Processed payroll can be marked as Paid.'
            ]);

    }


    $payroll->update([

        'status' => 'Paid',

        'payment_date' => now()->toDateString(),

    ]);


    return back()
        ->with(
            'success',
            'Payroll marked as paid successfully.'
        );
}
    #endregion paid
}