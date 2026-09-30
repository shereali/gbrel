<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Remove any current / legacy leads from the table
        DB::table('leads')->delete();

        // 2. Add softDeletes column to the leads table
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
