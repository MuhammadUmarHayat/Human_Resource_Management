<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('designation')?->id;

        return [

            'department_id'=>[
                'required',
                'exists:departments,id'
            ],

            'name'=>[
                'required',
                'max:100'
            ],

            'code'=>[
                'required',
                Rule::unique('designations')
                    ->ignore($id)
            ],

            'description'=>[
                'nullable'
            ],

            'status'=>[
                'boolean'
            ]

        ];
    }
}