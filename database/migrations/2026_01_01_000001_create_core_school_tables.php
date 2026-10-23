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
        // 1. School Profile Table
        Schema::create('school_profile', function (Blueprint $table) {
            $table->id();
            $table->uuid('owner_id')->nullable(); // UUID link to Auth user
            $table->string('school_name');
            $table->string('school_code')->nullable();
            $table->enum('plan', ['Basic', 'Classic', 'Super'])->default('Basic');
            $table->boolean('plan_locked')->default(false);
            $table->boolean('onboarding_completed')->default(false);
            $table->timestamp('subscription_expires_at')->nullable();
            $table->string('motto')->nullable();
            $table->text('school_address')->nullable();
            $table->string('location')->nullable();
            $table->string('email_address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('headteacher_name')->nullable();
            $table->text('logo_url')->nullable();
            $table->longText('school_stamp_svg')->nullable();
            $table->text('report_watermark')->nullable();
            $table->json('report_template_config')->nullable();
            $table->decimal('promotion_cutoff_average', 5, 2)->default(40.00);
            $table->timestamps();
        });

        // 2. Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->uuid('auth_id')->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('role', ['admin', 'headteacher', 'teacher', 'student', 'cashier'])->default('student');
            $table->foreignId('school_id')->nullable()->constrained('school_profile')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Academic Sessions Table
        Schema::create('sessions', function (Blueprint $table) {
            $table->id('session_id');
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->string('session_year'); // e.g. '2025/2026'
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 4. Academic Terms Table
        Schema::create('terms', function (Blueprint $table) {
            $table->id('term_id');
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('sessions', 'session_id')->cascadeOnDelete();
            $table->enum('term', ['First', 'Second', 'Third']);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('status')->default(false); // True if active term
            $table->boolean('results_locked')->default(false);
            $table->timestamps();

            $table->unique(['session_id', 'term'], 'unique_term_name_per_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('school_profile');
    }
};
