<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('label', 64)->nullable();
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('district', 128)->nullable();
            $table->string('city', 128)->nullable();
            $table->string('postal_code', 16)->nullable();
            $table->string('country', 64)->default('TR');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
