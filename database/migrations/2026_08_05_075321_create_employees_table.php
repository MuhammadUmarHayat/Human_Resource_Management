<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {

            $table->id();

            $table->string('employee_code')->unique();

            $table->foreignId('department_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('designation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name');

            $table->string('email')->unique();

            $table->string('phone',20);

            $table->string('cnic',20)->unique();

            $table->date('date_of_birth');

            $table->enum('gender',[
                'Male',
                'Female'
            ]);

            $table->date('joining_date');

            $table->decimal('basic_salary',12,2);

            $table->string('photo')->nullable();

            $table->text('address')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};