<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveType;
use Inertia\Inertia;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $leaveTypes = LeaveType::latest()
        ->paginate(10);

    return Inertia::render(
        'Admin/LeaveTypes/Index',
        [
            'leaveTypes' => $leaveTypes
        ]
    );
}
public function create()
{
    return Inertia::render(
        'Admin/LeaveTypes/Create'
    );
}

    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([

        'name' => 'required|max:100|unique:leave_types',

        'days_per_year' => 'required|integer|min:1',

        'is_paid' => 'required|boolean',

    ]);

    LeaveType::create($validated);

    return redirect()
        ->route('admin.leave-types.index')
        ->with('success', 'Leave Type created successfully.');
}

// public function storeApplication(Request $request)
// {
//     $validated = $request->validate([
//         'employee_id'   => 'required|exists:employees,id',
//         'leave_type_id' => 'required|exists:leave_types,id',
//         'from_date'     => 'required|date',
//         'to_date'       => 'required|date|after_or_equal:from_date',
//         'reason'        => 'required|min:10',
//     ]);

//     $validated['total_days'] =
//         \Carbon\Carbon::parse($validated['from_date'])
//             ->diffInDays(
//                 \Carbon\Carbon::parse($validated['to_date'])
//             ) + 1;

//     $validated['status'] = 'Pending';

//     \App\Models\Leave::create($validated);

//     return redirect()
//         ->route('admin.leaves.index')
//         ->with('success', 'Leave application submitted successfully.');
// }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
 public function edit(LeaveType $leaveType)
{
    return Inertia::render(
        'Admin/LeaveTypes/Edit',
        [
            'leaveType' => $leaveType
        ]
    );
}

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, LeaveType $leaveType)
{
    $validated = $request->validate([

        'name' => 'required|max:100|unique:leave_types,name,' . $leaveType->id,

        'days_per_year' => 'required|integer|min:1',

        'is_paid' => 'required|boolean',

    ]);

    $leaveType->update($validated);

    return redirect()
        ->route('admin.leave-types.index')
        ->with('success', 'Leave Type updated successfully.');
}
  
 public function destroy(LeaveType $leaveType)
{
    $leaveType->delete();

    return redirect()
        ->route('admin.leave-types.index')
        ->with('success', 'Leave Type deleted successfully.');
}
}
