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
        // 1. Fee Categories Table
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Fee Structures Table
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes', 'class_id')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('sessions', 'session_id')->cascadeOnDelete();
            $table->string('term'); // e.g. First | Second | Third
            $table->foreignId('fee_category_id')->nullable()->constrained('fee_categories')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['class_id', 'session_id', 'term', 'fee_category_id'], 'unique_class_session_term_fee');
        });

        // 3. Student General Payments Ledger Table
        Schema::create('student_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students', 'student_id')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('sessions', 'session_id')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms', 'term_id')->cascadeOnDelete();

            $table->decimal('amount_paid', 12, 2);
            $table->string('purpose')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('receipt_no')->nullable()->unique();
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('cashier_id')->nullable()->constrained('staff', 'staff_id')->nullOnDelete();
            $table->timestamp('payment_date')->useCurrent();

            $table->timestamps();
        });

        // 4. Student Itemized Fee Payments Table
        Schema::create('student_fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students', 'student_id')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('sessions', 'session_id')->cascadeOnDelete();
            $table->string('term');
            $table->foreignId('fee_category_id')->nullable()->constrained('fee_categories')->cascadeOnDelete();

            $table->string('receipt_no')->unique();
            $table->decimal('amount_paid', 12, 2);
            $table->timestamp('payment_date')->useCurrent();
            $table->string('payment_method')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('staff', 'staff_id')->nullOnDelete();

            $table->timestamps();
        });

        // 5. Student Fee Clearance Status Table
        Schema::create('student_fee_clearance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students', 'student_id')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('sessions', 'session_id')->cascadeOnDelete();
            $table->string('term');

            $table->string('clearance_status')->default('Pending'); // Cleared | Uncleared | Partial
            $table->foreignId('cleared_by')->nullable()->constrained('staff', 'staff_id')->nullOnDelete();
            $table->timestamp('clearance_date')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'session_id', 'term'], 'unique_student_session_term_clearance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fee_clearance');
        Schema::dropIfExists('student_fee_payments');
        Schema::dropIfExists('student_payments');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('fee_categories');
    }
};
