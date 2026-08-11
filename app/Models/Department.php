<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
    public function designations()
{
    return $this->hasMany(Designation::class);
}
}