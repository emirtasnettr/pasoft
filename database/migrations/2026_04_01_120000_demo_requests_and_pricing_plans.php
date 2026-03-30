<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_requests', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone', 32)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::table('app_settings', function (Blueprint $table) {
            $table->json('pricing_plans')->nullable()->after('site_description');
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn('pricing_plans');
        });

        Schema::dropIfExists('demo_requests');
    }
};
