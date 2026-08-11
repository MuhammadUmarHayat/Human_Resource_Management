<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [

        'employee_id',

        'month',
        'year',

        'basic_salary',

        'allowances',
        'overtime',
        'bonuses',
        'deductions',

        'gross_salary',
        'net_salary',

        'working_days',
        'present_days',
        'leave_days',
        'absent_days',

        'status',

        'payment_date',

        'remarks',

    ];


    protected $casts = [

        'month' => 'integer',

        'year' => 'integer',

        'basic_salary' => 'decimal:2',

        'allowances' => 'decimal:2',

        'overtime' => 'decimal:2',

        'bonuses' => 'decimal:2',

        'deductions' => 'decimal:2',

        'gross_salary' => 'decimal:2',

        'net_salary' => 'decimal:2',

        'working_days' => 'integer',

        'present_days' => 'integer',

        'leave_days' => 'integer',

        'absent_days' => 'integer',

        'payment_date' => 'date',

    ];


    /*
    |--------------------------------------------------------------------------
    | Employee
    |--------------------------------------------------------------------------
    */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class
        );
    }
}