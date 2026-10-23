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
        // 1. Homework assignments
        Schema::create('homeworks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->foreignId('class_id')->constrained('classes', 'class_id')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects', 'subject_id')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff', 'staff_id')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('sessions', 'session_id')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms', 'term_id')->nullOnDelete();
            $table->timestamp('due_date')->nullable();
            $table->text('file_url')->nullable();
            $table->string('file_name')->nullable();
            $table->decimal('max_score', 8, 2)->default(100.00);
            $table->timestamps();
        });

        // 2. Homework submissions
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('homework_id')->constrained('homeworks')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students', 'student_id')->cascadeOnDelete();
            $table->text('submission_text')->nullable();
            $table->text('file_url')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->decimal('score', 8, 2)->nullable();
            $table->text('comment')->nullable();
            $table->string('status')->default('submitted');
            $table->timestamps();
        });

        // 3. Communication Campaigns
        Schema::create('communication_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->string('title');
            $table->string('type'); // PTA, FEE_REMINDER, RESULT, HOLIDAY, CUSTOM
            $table->string('channel')->default('email');
            $table->uuid('template_id')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('status')->default('draft'); // draft, queued, processing, sent, completed, failed
            $table->timestamp('scheduled_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
        });

        // 4. Communication Templates
        Schema::create('communication_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->nullable(); // PTA, FEE_REMINDER, RESULT, HOLIDAY
            $table->string('subject');
            $table->longText('html_template');
            $table->json('variables')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // 5. Communication Recipients
        Schema::create('communication_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignUuid('campaign_id')->constrained('communication_campaigns')->cascadeOnDelete();
            $table->string('user_type')->nullable(); // student | parent | staff
            $table->bigInteger('user_id')->nullable();
            $table->string('email');
            $table->json('payload')->nullable();
            $table->string('status')->default('pending'); // pending, processing, sent, failed
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // 6. Communication Logs
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignUuid('campaign_id')->nullable()->constrained('communication_campaigns')->nullOnDelete();
            $table->foreignUuid('recipient_id')->nullable()->constrained('communication_recipients')->nullOnDelete();
            $table->string('event')->nullable(); // sent, delivered, opened, bounced, failed
            $table->string('provider_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 7. Communication Settings
        Schema::create('communication_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('school_id')->unique()->constrained('school_profile')->cascadeOnDelete();
            $table->string('sender_name')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('reply_to')->nullable();
            $table->text('logo_url')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('primary_color')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_settings');
        Schema::dropIfExists('communication_logs');
        Schema::dropIfExists('communication_recipients');
        Schema::dropIfExists('communication_templates');
        Schema::dropIfExists('communication_campaigns');
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homeworks');
    }
};
