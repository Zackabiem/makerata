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
        // 1. Terminal Score Sheet Table
        Schema::create('terminal_score_sheet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students', 'student_id')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes', 'class_id')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects', 'subject_id')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('sessions', 'session_id')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms', 'term_id')->cascadeOnDelete();

            $table->integer('first_test')->nullable()->default(0);
            $table->integer('second_test')->nullable()->default(0);
            $table->integer('third_test')->nullable()->default(0);
            $table->integer('exam_score')->nullable()->default(0);

            // Stored generated virtual column for automatic MySQL summation
            $table->integer('total_score')->storedAs('COALESCE(first_test, 0) + COALESCE(second_test, 0) + COALESCE(third_test, 0) + COALESCE(exam_score, 0)');

            $table->string('grade')->nullable();
            $table->string('remark')->nullable();
            $table->decimal('subject_average', 5, 2)->nullable();
            $table->integer('highest_score')->nullable();
            $table->integer('lowest_score')->nullable();
            $table->integer('position_in_subject')->nullable();

            $table->integer('term_position')->nullable();
            $table->decimal('term_total', 12, 2)->default(0);
            $table->decimal('term_average', 5, 2)->default(0);
            $table->integer('number_of_subjects')->default(0);

            $table->text('form_master_remarks')->nullable();
            $table->text('headteacher_remarks')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'term_id'], 'unique_student_subject_term');
        });

        // 2. Annual Remarks Table
        Schema::create('annual_remarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students', 'student_id')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes', 'class_id')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('sessions', 'session_id')->cascadeOnDelete();
            $table->text('form_master_remarks')->nullable();
            $table->text('headteacher_remarks')->nullable();
            $table->enum('generalremark', ['promoted', 'repeat'])->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'session_id']);
        });

        // 3. Psychomotor & Affective Skills Table
        Schema::create('psychomotor_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students', 'student_id')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes', 'class_id')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('sessions', 'session_id')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms', 'term_id')->cascadeOnDelete();

            // Psychomotor Domains (1-5 range)
            $table->integer('handwriting')->default(0);
            $table->integer('fluency')->default(0);
            $table->integer('games')->default(0);
            $table->integer('sports')->default(0);
            $table->integer('gymnastics')->default(0);
            $table->integer('crafts')->default(0);
            $table->integer('drawing_painting')->default(0);
            $table->integer('musical_skills')->default(0);

            // Affective Domains (1-5 range)
            $table->integer('punctuality')->default(0);
            $table->integer('neatness')->default(0);
            $table->integer('politeness')->default(0);
            $table->integer('honesty')->default(0);
            $table->integer('relationship_with_students')->default(0);
            $table->integer('self_control')->default(0);
            $table->integer('attentiveness')->default(0);
            $table->integer('persistence')->default(0);

            $table->timestamps();

            $table->unique(['student_id', 'term_id']);
        });

        // 4. Attendance Records Table
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students', 'student_id')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes', 'class_id')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('sessions', 'session_id')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms', 'term_id')->cascadeOnDelete();

            $table->integer('days_present')->default(0);
            $table->integer('days_absent')->default(0);
            $table->integer('total_days_possible')->default(0);

            $table->timestamps();

            $table->unique(['student_id', 'term_id']);
        });

        // 5. Promotion Audit Log Table
        Schema::create('promotion_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students', 'student_id')->cascadeOnDelete();
            $table->foreignId('old_class_id')->nullable()->constrained('classes', 'class_id')->nullOnDelete();
            $table->foreignId('new_class_id')->nullable()->constrained('classes', 'class_id')->nullOnDelete();
            $table->foreignId('old_session_id')->nullable()->constrained('sessions', 'session_id')->nullOnDelete();
            $table->foreignId('new_session_id')->nullable()->constrained('sessions', 'session_id')->nullOnDelete();
            $table->string('action_type')->nullable(); // PROMOTED | REPEATED | GRADUATED | WITHDRAWN
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_log');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('psychomotor_skills');
        Schema::dropIfExists('annual_remarks');
        Schema::dropIfExists('terminal_score_sheet');
    }
};
