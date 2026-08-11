<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\LeaveType;
use Inertia\Inertia;
use Carbon\Carbon;

use App\Models\Leave;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;





class LeaveApplicationController extends Controller
{
    
#region leave balance management
private function getLeaveBalances($employeeId)
{
    $leaveTypes = LeaveType::orderBy('name')->get();

    return $leaveTypes->map(function ($leaveType) use ($employeeId) {

        $usedDays = Leave::where(
                'employee_id',
                $employeeId
            )
            ->where(
                'leave_type_id',
                $leaveType->id
            )
            ->where(
                'status',
                'Approved'
            )
            ->sum('total_days');


        $remainingDays =
            max(
                0,
                $leaveType->days_per_year - $usedDays
            );


        return [
            'id' => $leaveType->id,

            'name' => $leaveType->name,

            'days_per_year' =>
                $leaveType->days_per_year,

            'used_days' =>
                $usedDays,

            'remaining_days' =>
                $remainingDays,

            'is_paid' =>
                $leaveType->is_paid,
        ];

    })->values();
}

#endregion

#region resource
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $leaves = Leave::with([
        'employee',
        'leaveType'
    ])
    ->latest()
    ->paginate(10);


    $employees = Employee::orderBy(
        'first_name'
    )->get();


    $selectedEmployeeId =
        $request->employee_id
        ?? $employees->first()?->id;


    $leaveBalances = $selectedEmployeeId
        ? $this->getLeaveBalances(
            $selectedEmployeeId
        )
        : collect();


    return Inertia::render(
        'Admin/LeaveApplications/Index',
        [

            'leaves' => $leaves,

            'employees' => $employees,

            'leaveBalances' => $leaveBalances,

            'selectedEmployeeId' =>
                $selectedEmployeeId,

        ]
    );
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
{
    return Inertia::render(
        'Admin/LeaveApplications/Create',
        [

            'employees' => Employee::orderBy('first_name')->get(),

            'leaveTypes' => LeaveType::orderBy('name')->get()
            
        ]
    );
}

    /**
     * Store a newly created resource in storage.
     */
 

public function store(Request $request)
{
    $validated = $request->validate([

        'employee_id' =>
            'required|exists:employees,id',

        'leave_type_id' =>
            'required|exists:leave_types,id',

        'from_date' =>
            'required|date',

        'to_date' =>
            'required|date|after_or_equal:from_date',

        'reason' =>
            'required|min:10',

    ]);


    /*
    |--------------------------------------------------------------------------
    | Check Leave Date Overlap
    |--------------------------------------------------------------------------
    */

    $overlap = Leave::where(
            'employee_id',
            $validated['employee_id']
        )
        ->whereIn(
            'status',
            ['Pending', 'Approved']
        )
        ->where(function ($query) use ($validated) {

            $query->whereBetween(
                'from_date',
                [
                    $validated['from_date'],
                    $validated['to_date']
                ]
            )

            ->orWhereBetween(
                'to_date',
                [
                    $validated['from_date'],
                    $validated['to_date']
                ]
            )

            ->orWhere(function ($query) use ($validated) {

                $query->where(
                    'from_date',
                    '<=',
                    $validated['from_date']
                )

                ->where(
                    'to_date',
                    '>=',
                    $validated['to_date']
                );

            });

        })
        ->exists();


    if ($overlap) {

        return back()
            ->withErrors([
                'from_date' =>
                    'This employee already has a pending or approved leave for the selected dates.'
            ])
            ->withInput();

    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Total Days
    |--------------------------------------------------------------------------
    */

    $validated['total_days'] =
        Carbon::parse(
            $validated['from_date']
        )->diffInDays(
            Carbon::parse(
                $validated['to_date']
            )
        ) + 1;


    /*
    |--------------------------------------------------------------------------
    | Default Status
    |--------------------------------------------------------------------------
    */

    $validated['status'] = 'Pending';

/*
|--------------------------------------------------------------------------
| Check Leave Balance
|--------------------------------------------------------------------------
*/

$leaveType = LeaveType::findOrFail(
    $validated['leave_type_id']
);


$usedDays = Leave::where(
        'employee_id',
        $validated['employee_id']
    )
    ->where(
        'leave_type_id',
        $validated['leave_type_id']
    )
    ->where(
        'status',
        'Approved'
    )
    ->sum('total_days');


$remainingDays = max(
    0,
    $leaveType->days_per_year - $usedDays
);
$validated['total_days'] =
    Carbon::parse(
        $validated['from_date']
    )->diffInDays(
        Carbon::parse(
            $validated['to_date']
        )
    ) + 1;
    if ($validated['total_days'] > $remainingDays) {

    return back()
        ->withErrors([
            'to_date' =>
                "You only have {$remainingDays} {$leaveType->name} days remaining."
        ])
        ->withInput();

}


    /*
    |--------------------------------------------------------------------------
    | Create Leave
    |--------------------------------------------------------------------------
    */

    Leave::create($validated);


    return redirect()
        ->route(
            'admin.leave-applications.index'
        )
        ->with(
            'success',
            'Leave application submitted successfully.'
        );
}
    /**
     * Display the specified resource.
     */
    

    public function show(string $id)
{
    $leave = Leave::with([
        'employee.department',
        'employee.designation',
        'leaveType',
        'approver',
    ])->findOrFail($id);

    return Inertia::render(
        'Admin/LeaveApplications/Show',
        [
            'leave' => $leave,
        ]
    );
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
{
    $leave = Leave::findOrFail($id);

    return Inertia::render(
        'Admin/LeaveApplications/Edit',
        [
            'leave' => $leave,
            'employees' => Employee::orderBy('first_name')->get(),
            'leaveTypes' => LeaveType::orderBy('name')->get(),
        ]
    );
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $leave = Leave::findOrFail($id);

    $validated = $request->validate([

        'employee_id' => 'required|exists:employees,id',

        'leave_type_id' => 'required|exists:leave_types,id',

        'from_date' => 'required|date',

        'to_date' => 'required|date|after_or_equal:from_date',

        'reason' => 'required|min:10',

    ]);

    $validated['total_days'] =
        Carbon::parse($validated['from_date'])
            ->diffInDays(
                Carbon::parse($validated['to_date'])
            ) + 1;

    $leave->update($validated);

    return redirect()
        ->route('admin.leave-applications.index')
        ->with(
            'success',
            'Leave application updated successfully.'
        );
}

    /**
     * Remove the specified resource from storage.
     */
   public function destroy(string $id)
{
    $leave = Leave::findOrFail($id);

    $leave->delete();

    return redirect()
        ->route('admin.leave-applications.index')
        ->with(
            'success',
            'Leave application deleted successfully.'
        );
}

#endregion resource

#region leave applications approved /reject
public function approve(string $id)
{
    $leave = Leave::with('leaveType')->findOrFail($id);

    if ($leave->status !== 'Pending') {

        return redirect()
            ->back()
            ->with(
                'error',
                'Only pending leave applications can be approved.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Check Overlapping Leave
    |--------------------------------------------------------------------------
    */

    $overlap = Leave::where(
            'employee_id',
            $leave->employee_id
        )
        ->whereIn(
            'status',
            ['Pending', 'Approved']
        )
        ->where(
            'id',
            '!=',
            $leave->id
        )
        ->where(function ($query) use ($leave) {

            $query->whereBetween(
                'from_date',
                [
                    $leave->from_date,
                    $leave->to_date
                ]
            )

            ->orWhereBetween(
                'to_date',
                [
                    $leave->from_date,
                    $leave->to_date
                ]
            )

            ->orWhere(function ($query) use ($leave) {

                $query->where(
                    'from_date',
                    '<=',
                    $leave->from_date
                )

                ->where(
                    'to_date',
                    '>=',
                    $leave->to_date
                );

            });

        })
        ->exists();


    if ($overlap) {

        return redirect()
            ->back()
            ->with(
                'error',
                'This leave cannot be approved because it overlaps with another pending or approved leave.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve Leave + Update Attendance
    |--------------------------------------------------------------------------
    */

    DB::transaction(function () use ($leave) {

        $leave->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'remarks' => null,
        ]);


        $startDate = Carbon::parse(
            $leave->from_date
        );

        $endDate = Carbon::parse(
            $leave->to_date
        );


        for (
            $date = $startDate->copy();
            $date->lte($endDate);
            $date->addDay()
        ) {

            Attendance::updateOrCreate(

                [
                    'employee_id' =>
                        $leave->employee_id,

                    'attendance_date' =>
                        $date->format('Y-m-d'),
                ],

                [
                    'check_in' => null,

                    'check_out' => null,

                    'working_hours' => 0,

                    'status' => 'Leave',

                    'remarks' =>
                        'Leave: ' .
                        $leave->leaveType->name,
                ]

            );

        }

    });


    return redirect()
        ->back()
        ->with(
            'success',
            'Leave approved and attendance updated successfully.'
        );
}
public function reject(Request $request, string $id)
{
    $leave = Leave::findOrFail($id);

    if ($leave->status !== 'Pending') {
        return redirect()
            ->back()
            ->with(
                'error',
                'Only pending leave applications can be rejected.'
            );
    }

    $validated = $request->validate([
        'remarks' => 'required|string|min:5|max:1000',
    ]);

    $leave->update([
        'status' => 'Rejected',
        'approved_by' => auth()->id(),
        'remarks' => $validated['remarks'],
    ]);

    return redirect()
        ->back()
        ->with(
            'success',
            'Leave application rejected successfully.'
        );
}

#endregion leave application approved /reject




}
