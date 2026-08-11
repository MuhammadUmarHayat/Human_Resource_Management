<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department')
            ? $this->route('department')->id
            : null;

        return [

            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('departments')->ignore($departmentId),
            ],

            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('departments')->ignore($departmentId),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'boolean',
            ],

        ];
    }
}