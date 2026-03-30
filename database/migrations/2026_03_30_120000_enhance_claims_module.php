<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->string('claim_type', 128)->nullable()->after('file_number');
            $table->decimal('amount', 14, 2)->nullable()->after('claim_type');
            $table->decimal('paid_amount', 14, 2)->default(0)->after('amount');
            $table->timestamp('last_activity_at')->nullable()->after('handler_user_id');
        });

        DB::table('claims')->where('status', 'open')->update(['status' => 'opened']);
        DB::table('claims')->where('status', 'pending')->update(['status' => 'documents_pending']);

        DB::table('claims')->whereNull('last_activity_at')->update([
            'last_activity_at' => DB::raw('COALESCE(updated_at, created_at)'),
        ]);
    }

    public function down(): void
    {
        DB::table('claims')->where('status', 'opened')->update(['status' => 'open']);
        DB::table('claims')->where('status', 'documents_pending')->update(['status' => 'pending']);

        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['claim_type', 'amount', 'paid_amount', 'last_activity_at']);
        });
    }
};
