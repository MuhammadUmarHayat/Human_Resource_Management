<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Employee extends Model
{
    protected $fillable = [

        'employee_code',
        'department_id',
        'designation_id',

        'first_name',
        'last_name',

        'email',
        'phone',
        'cnic',

        'date_of_birth',
        'gender',

        'joining_date',

        'basic_salary',

        'photo',

        'address',

        'status'
    ];

    protected $casts=[
        'status'=>'boolean',
        'date_of_birth'=>'date',
        'joining_date'=>'date'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
    public function attendances(): HasMany
{
    return $this->hasMany(Attendance::class);
}

public function leaves()
{
    return $this->hasMany(Leave::class);
}

public function payrolls(): HasMany
{
    return $this->hasMany(
        Payroll::class
    );
}

}