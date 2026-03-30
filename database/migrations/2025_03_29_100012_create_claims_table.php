<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('policy_id')->nullable()->constrained('policies')->nullOnDelete();
            $table->foreignId('insurance_company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_number');
            $table->string('status', 32)->default('open');
            $table->text('customer_notice_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('handler_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('policy_id');
            $table->index('insurance_company_id');
            $table->index('handler_user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
