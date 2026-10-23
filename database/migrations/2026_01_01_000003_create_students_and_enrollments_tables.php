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
        // 1. Students Register Table
        Schema::create('students', function (Blueprint $table) {
            $table->id('student_id');
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('admission_no')->nullable()->unique();
            $table->string('surname');
            $table->string('first_name');
            $table->string('other_name')->nullable();
            $table->string('gender')->default('Male');
            $table->date('date_of_birth')->nullable();
            $table->text('passport_url')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('parent_phone')->nullable();
            $table->text('home_address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->timestamps();
        });

        // 2. Class Enrollments Table
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id('enrollment_id');
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students', 'student_id')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes', 'class_id')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('sessions', 'session_id')->cascadeOnDelete();
            $table->string('status')->default('ACTIVE'); // ACTIVE, SUSPENDED, WITHDRAWN, GRADUATED
            $table->boolean('is_current')->default(true);
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('exited_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'session_id', 'class_id']);
        });

        // 3. Subject Teacher Assignments Table
        Schema::create('subject_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff', 'staff_id')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects', 'subject_id')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes', 'class_id')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('sessions', 'session_id')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['staff_id', 'class_id', 'subject_id', 'session_id'], 'unique_staff_class_subject_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_assignments');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('students');
    }
};
