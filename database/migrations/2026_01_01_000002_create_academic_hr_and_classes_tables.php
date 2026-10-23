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
        // 1. Grading Systems Table
        Schema::create('grading_systems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // 2. Grading Scales Table
        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('grading_system_id')->nullable()->constrained('grading_systems')->cascadeOnDelete();
            $table->integer('min_score');
            $table->integer('max_score');
            $table->string('grade');
            $table->string('remark')->nullable();
            $table->timestamps();
        });

        // 3. Staff Table
        Schema::create('staff', function (Blueprint $table) {
            $table->id('staff_id');
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->cascadeOnDelete();
            $table->string('surname');
            $table->string('first_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('role')->default('teacher'); // headteacher, teacher, cashier, registrar
            $table->string('status')->default('pending'); // pending, active, suspended
            $table->timestamps();
        });

        // 4. Classes Table
        Schema::create('classes', function (Blueprint $table) {
            $table->id('class_id');
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->string('arm_name'); // e.g. JSS1-A
            $table->string('class_name'); // e.g. JSS1
            $table->integer('level_order'); // Sorting order
            $table->string('category')->nullable(); // Nursery, Basic, Junior_Secondary, Senior_Secondary
            $table->string('arm')->nullable(); // A, B, Science
            $table->foreignId('grading_system_id')->nullable()->constrained('grading_systems')->nullOnDelete();
            $table->foreignId('form_master_id')->nullable()->constrained('staff', 'staff_id')->nullOnDelete();
            $table->integer('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_graduation_class')->default(false);
            $table->timestamps();

            $table->unique(['level_order', 'arm', 'school_id']);
        });

        // 5. Subjects Table
        Schema::create('subjects', function (Blueprint $table) {
            $table->id('subject_id');
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // 6. Class Subjects (Curriculum Mapping) Table
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes', 'class_id')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects', 'subject_id')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['class_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_subjects');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('grading_scales');
        Schema::dropIfExists('grading_systems');
    }
};
