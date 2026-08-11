<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    /**
     * Employee Dashboard
     */

    //  public function index(): Response
    // {//test
    //     $employee = $this->getEmployee();

    //     return Inertia::render(
    //         'Employee/Test',
    //         [
    //             'employee' => $employee,
    //         ]
    //     );
        
    // }
    // 
   
    /**
     * Get currently logged-in employee.
     */
//     private function getEmployee(): ?Employee
// {//test
//     return Employee::with([
//         'department',
//         'designation',
//     ])
//     ->where('email', auth()->user()->email)
//     ->first();
// }
public function index(): Response
{//origional
    $employee = $this->getEmployee();

    return Inertia::render(
        'Employee/Dashboard',
        [
            'employee' => $employee,
        ]
    );
}
    private function getEmployee(): Employee
    {// origional
        return Employee::with([
            'department',
            'designation',
        ])
        ->where(
            'email',
            auth()->user()->email
        )
        //->first();
        ->firstOrFail();
    }
}