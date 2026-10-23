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
        // 1. SaaS Subscription Plans Catalog
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 50)->unique();
            $table->string('slug', 50)->unique();
            $table->text('description')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('price_per_student', 12, 2)->default(0);
            $table->string('billing_period', 20)->default('term');
            $table->integer('max_students')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Feature Catalog
        Schema::create('feature_catalog', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('feature_key', 100)->unique();
            $table->string('feature_name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Subscription Plan Features Matrix
        Schema::create('subscription_plan_features', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->string('feature_key', 100);
            $table->timestamps();

            $table->unique(['plan_id', 'feature_key']);
        });

        // 4. School Active Subscriptions
        Schema::create('school_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('subscription_plans');
            $table->string('status', 30)->default('active');
            $table->integer('student_count')->default(0);
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('student_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('billing_period', 20)->default('term');
            $table->string('academic_year', 50)->nullable();
            $table->string('term', 50)->nullable();
            $table->string('reference', 255)->nullable();
            $table->integer('grace_days')->default(30);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_billing_date')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        // 5. Subscription Invoices
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number', 50)->unique();
            $table->foreignUuid('school_subscription_id')->constrained('school_subscriptions')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->integer('student_count')->default(0);
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('student_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status', 30)->default('unpaid');
            $table->string('billing_period', 20)->default('term');
            $table->date('issued_date');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('paystack_reference', 255)->nullable();
            $table->bigInteger('paystack_transaction_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('gateway_response')->nullable();
            $table->timestamps();
        });

        // 6. Subscription Payments Log
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('subscription_invoices')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50)->nullable();
            $table->string('transaction_reference', 255)->nullable();
            $table->string('payment_gateway', 50)->nullable();
            $table->string('status', 30)->default('successful');
            $table->timestamp('paid_at')->useCurrent();
            $table->timestamps();
        });

        // 7. School Feature Overrides
        Schema::create('school_feature_overrides', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('school_id')->constrained('school_profile')->cascadeOnDelete();
            $table->string('feature_key', 100);
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_feature_overrides');
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('school_subscriptions');
        Schema::dropIfExists('subscription_plan_features');
        Schema::dropIfExists('feature_catalog');
        Schema::dropIfExists('subscription_plans');
    }
};
