<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'employee_id' => 'required|exists:employees,id',

            'attendance_date' => 'required|date',

            'check_in' => 'nullable',

            'check_out' => 'nullable',

            'working_hours' => 'nullable|numeric|min:0',

            'status' => 'required|in:Present,Absent,Leave,Half Day',

            'remarks' => 'nullable|max:500',

        ];
    }
}