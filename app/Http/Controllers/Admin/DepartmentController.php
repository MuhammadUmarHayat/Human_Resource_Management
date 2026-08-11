<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    /**
     * Display Department List
     */
    public function index(Request $request)
    {
        $departments = Department::query()

            ->when($request->search, function ($query) use ($request) {

                $query->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('code', 'like', '%' . $request->search . '%');

            })

            ->latest()

            ->paginate(10)

            ->withQueryString();

        return Inertia::render(
            'Admin/Departments/Index',
            [
                'departments' => $departments,
                'filters' => [
                    'search' => $request->search,
                ],
            ]
        );
    }

    /**
     * Create Form
     */
    public function create()
    {
        return Inertia::render('Admin/Departments/Create');
    }

    /**
     * Store Department
     */
    public function store(DepartmentRequest $request)
    {
        Department::create($request->validated());

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Show
     */
    public function show(Department $department)
    {
        return Inertia::render(
            'Admin/Departments/Show',
            [
                'department' => $department
            ]
        );
    }

    /**
     * Edit
     */
    public function edit(Department $department)
    {
        return Inertia::render(
            'Admin/Departments/Edit',
            [
                'department' => $department
            ]
        );
    }

    /**
     * Update
     */
    public function update(
        DepartmentRequest $request,
        Department $department
    ) {
        $department->update($request->validated());

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Delete
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()
            ->back()
            ->with('success', 'Department deleted successfully.');
    }
}