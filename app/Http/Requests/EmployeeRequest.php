<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    $id = $this->route('employee')?->id;

    return [

        'department_id'=>'required|exists:departments,id',

        'designation_id'=>'required|exists:designations,id',

        'first_name'=>'required|max:100',

        'last_name'=>'required|max:100',

        'email'=>"required|email|unique:employees,email,$id",

        'phone'=>'required|max:20',

        'cnic'=>"required|unique:employees,cnic,$id",

        'date_of_birth'=>'required|date',

        'gender'=>'required',

        'joining_date'=>'required|date',

        'basic_salary'=>'required|numeric|min:0',

        'address'=>'nullable',

        'status'=>'boolean',

        'photo'=>'nullable|image|max:2048'

    ];
}
}
