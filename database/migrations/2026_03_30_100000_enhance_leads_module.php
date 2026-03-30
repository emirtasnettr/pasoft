<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('next_follow_up_at');
        });

        DB::table('leads')->whereNull('last_activity_at')->update([
            'last_activity_at' => DB::raw('COALESCE(updated_at, created_at)'),
        ]);

        Schema::create('lead_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_notes');

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('last_activity_at');
        });
    }
};
