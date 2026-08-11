<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    /**
     * Display employees.
     */
    public function index(Request $request)
    {
        $employees = Employee::with([
            'department',
            'designation'
        ])

        ->when($request->search, function ($query) use ($request) {

            $query->where(function ($q) use ($request) {

                $q->where('employee_code', 'like', "%{$request->search}%")
                  ->orWhere('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('cnic', 'like', "%{$request->search}%");

            });

        })

        ->latest()

        ->paginate(10)

        ->withQueryString();

        return Inertia::render(
            'Admin/Employees/Index',
            [
                'employees' => $employees,

                'filters' => [
                    'search' => $request->search
                ]
            ]
        );
    }

    /**
     * Create page.
     */
    public function create()
    {
        return Inertia::render(
            'Admin/Employees/Create',
            [

                'departments' => Department::orderBy('name')->get(),

                'designations' => Designation::orderBy('name')->get()

            ]
        );
    }

    /**
     * Store employee.
     */
    public function store(EmployeeRequest $request)
    {
        $data = $request->validated();

        $nextId = Employee::max('id') + 1;

        $data['employee_code'] =
            'EMP-' .
            str_pad($nextId, 5, '0', STR_PAD_LEFT);

        if ($request->hasFile('photo')) {

            $data['photo'] = $request
                ->file('photo')
                ->store('employees', 'public');
        }

        Employee::create($data);

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Show employee.
     */
    public function show(Employee $employee)
    {
        $employee->load([
            'department',
            'designation'
        ]);

        return Inertia::render(
            'Admin/Employees/Show',
            [
                'employee' => $employee
            ]
        );
    }

    /**
     * Edit page.
     */
    public function edit(Employee $employee)
    {
        $employee->date_of_birth = optional($employee->date_of_birth)->format('Y-m-d');
         $employee->joining_date = optional($employee->joining_date)->format('Y-m-d');

return Inertia::render('Admin/Employees/Edit', [
    'employee' => $employee,
    'departments' => Department::orderBy('name')->get(),
    'designations' => Designation::orderBy('name')->get(),
]);
    }

    /**
     * Update employee.
     */
    public function update(
    EmployeeRequest $request,
    Employee $employee
)
{
    $data = $request->validated();

    // Keep old photo by default
    $data['photo'] = $employee->photo;

    // Upload new photo
    if ($request->hasFile('photo')) {

        // Delete old photo
        if (
            $employee->photo &&
            Storage::disk('public')->exists($employee->photo)
        ) {
            Storage::disk('public')->delete($employee->photo);
        }

        $data['photo'] = $request
            ->file('photo')
            ->store('employees', 'public');
    }

    $employee->update($data);

    return redirect()
        ->route('admin.employees.index')
        ->with(
            'success',
            'Employee updated successfully.'
        );
}

    /**
     * Delete employee.
     */
    public function destroy(Employee $employee)
    {
        if ($employee->photo) {

            Storage::disk('public')
                ->delete($employee->photo);
        }

        $employee->delete();

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}