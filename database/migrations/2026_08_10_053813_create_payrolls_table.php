<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('payrolls', function (Blueprint $table) {

        $table->id();

        $table->foreignId('employee_id')
            ->constrained('employees')
            ->cascadeOnDelete();

        $table->unsignedTinyInteger('month');

        $table->unsignedSmallInteger('year');

        $table->decimal('basic_salary', 12, 2)
            ->default(0);

        $table->decimal('allowances', 12, 2)
            ->default(0);

        $table->decimal('overtime', 12, 2)
            ->default(0);

        $table->decimal('bonuses', 12, 2)
            ->default(0);

        $table->decimal('deductions', 12, 2)
            ->default(0);

        $table->decimal('gross_salary', 12, 2)
            ->default(0);

        $table->decimal('net_salary', 12, 2)
            ->default(0);

        $table->unsignedInteger('working_days')
            ->default(0);

        $table->unsignedInteger('present_days')
            ->default(0);

        $table->unsignedInteger('leave_days')
            ->default(0);

        $table->unsignedInteger('absent_days')
            ->default(0);

        $table->enum('status', [
            'Draft',
            'Processed',
            'Paid'
        ])->default('Draft');

        $table->date('payment_date')
            ->nullable();

        $table->text('remarks')
            ->nullable();

        $table->timestamps();

        $table->unique([
            'employee_id',
            'month',
            'year'
        ]);

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
