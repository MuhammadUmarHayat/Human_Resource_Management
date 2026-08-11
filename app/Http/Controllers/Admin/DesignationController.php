<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DesignationRequest;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        $designations = Designation::with('department')
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'Admin/Designations/Index',
            [
                'designations'=>$designations,
                'filters'=>[
                    'search'=>$request->search
                ]
            ]
        );
    }

    public function create()
    {
        return Inertia::render(
            'Admin/Designations/Create',
            [
                'departments'=>Department::orderBy('name')
                    ->get(['id','name'])
            ]
        );
    }

    public function store(DesignationRequest $request)
    {
        Designation::create($request->validated());

        return redirect()
            ->route('admin.designations.index')
            ->with('success','Designation created successfully.');
    }

    public function edit(Designation $designation)
    {
        return Inertia::render(
            'Admin/Designations/Edit',
            [
                'designation'=>$designation,
                'departments'=>Department::orderBy('name')
                    ->get(['id','name'])
            ]
        );
    }

    public function update(
        DesignationRequest $request,
        Designation $designation
    )
    {
        $designation->update($request->validated());

        return redirect()
            ->route('admin.designations.index')
            ->with('success','Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        $designation->delete();

        return back()
            ->with('success','Designation deleted.');
    }
}