<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('policy_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('insurance_company_id')->constrained()->restrictOnDelete();
            $table->string('policy_number');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->decimal('premium_amount', 14, 2)->default(0);
            $table->decimal('commission_amount', 14, 2)->default(0);
            $table->decimal('commission_collected', 14, 2)->default(0);
            $table->string('premium_payment_status', 32)->default('pending');
            $table->text('coverage_details')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('renewal_status', 32)->default('active');
            $table->string('renewal_pipeline_stage', 64)->nullable();
            $table->unsignedSmallInteger('renewal_reminder_days')->default(30);
            $table->timestamp('last_renewal_reminder_at')->nullable();
            $table->json('cross_sell_suggestions')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('policy_type_id');
            $table->index('insurance_company_id');
            $table->index('owner_user_id');
            $table->index('ends_at');
            $table->index('renewal_status');
            $table->index('policy_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
