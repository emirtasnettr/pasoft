<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurance_companies', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('code');
            $table->string('contact_person')->nullable()->after('contact_email');
            $table->decimal('default_commission_percent', 6, 2)->nullable()->after('contact_person');
            $table->boolean('api_enabled')->default(false)->after('is_active');
            $table->boolean('quote_integration_enabled')->default(false)->after('api_enabled');
        });

        Schema::table('policy_types', function (Blueprint $table) {
            $table->text('description')->nullable()->after('slug');
            $table->string('color', 16)->default('#6366f1')->after('category');
            $table->string('icon', 64)->default('rectangle-stack')->after('color');
            $table->decimal('default_commission_percent', 6, 2)->nullable()->after('icon');
            $table->unsignedSmallInteger('renewal_reminder_days')->default(30)->after('default_commission_percent');
        });

        Schema::create('insurance_company_policy_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('policy_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('commission_percent', 6, 2);
            $table->timestamps();

            $table->unique(['insurance_company_id', 'policy_type_id'], 'ic_pt_company_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_company_policy_type');

        Schema::table('policy_types', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'color',
                'icon',
                'default_commission_percent',
                'renewal_reminder_days',
            ]);
        });

        Schema::table('insurance_companies', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path',
                'contact_person',
                'default_commission_percent',
                'api_enabled',
                'quote_integration_enabled',
            ]);
        });
    }
};
