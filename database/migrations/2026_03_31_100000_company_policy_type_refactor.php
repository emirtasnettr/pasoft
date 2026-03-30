<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_policy_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('policy_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('commission_rate', 6, 2);
            $table->decimal('min_commission', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['insurance_company_id', 'policy_type_id']);
        });

        if (Schema::hasTable('insurance_company_policy_type')) {
            $rows = DB::table('insurance_company_policy_type')->get();
            foreach ($rows as $row) {
                DB::table('company_policy_type')->insert([
                    'insurance_company_id' => $row->insurance_company_id,
                    'policy_type_id' => $row->policy_type_id,
                    'commission_rate' => $row->commission_percent,
                    'min_commission' => null,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);
            }
            Schema::dropIfExists('insurance_company_policy_type');
        }

        if (Schema::hasColumn('insurance_companies', 'default_commission_percent')) {
            Schema::table('insurance_companies', function (Blueprint $table) {
                $table->dropColumn('default_commission_percent');
            });
        }

        if (Schema::hasColumn('policy_types', 'default_commission_percent')) {
            Schema::table('policy_types', function (Blueprint $table) {
                $table->dropColumn('default_commission_percent');
            });
        }
    }

    public function down(): void
    {
        Schema::create('insurance_company_policy_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('policy_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('commission_percent', 6, 2);
            $table->timestamps();

            $table->unique(['insurance_company_id', 'policy_type_id']);
        });

        if (Schema::hasTable('company_policy_type')) {
            $rows = DB::table('company_policy_type')->get();
            foreach ($rows as $row) {
                DB::table('insurance_company_policy_type')->insert([
                    'insurance_company_id' => $row->insurance_company_id,
                    'policy_type_id' => $row->policy_type_id,
                    'commission_percent' => $row->commission_rate,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);
            }
            Schema::dropIfExists('company_policy_type');
        }

        Schema::table('insurance_companies', function (Blueprint $table) {
            $table->decimal('default_commission_percent', 6, 2)->nullable()->after('contact_person');
        });

        Schema::table('policy_types', function (Blueprint $table) {
            $table->decimal('default_commission_percent', 6, 2)->nullable()->after('icon');
        });
    }
};
