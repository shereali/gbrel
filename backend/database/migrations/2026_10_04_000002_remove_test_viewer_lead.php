<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Removes ONLY the lead / viewing record named "Test Viewer".
     * All other leads and viewings remain intact.
     */
    public function up(): void
    {
        if (Schema::hasTable('leads')) {
            // Find lead IDs for "Test Viewer"
            $leadIds = DB::table('leads')
                ->where('name', 'Test Viewer')
                ->orWhere('name', 'like', '%Test Viewer%')
                ->pluck('id');

            if ($leadIds->isNotEmpty()) {
                // Remove related lead activities if lead_activities table exists
                if (Schema::hasTable('lead_activities')) {
                    DB::table('lead_activities')->whereIn('lead_id', $leadIds)->delete();
                }

                // Delete only the "Test Viewer" lead(s)
                DB::table('leads')->whereIn('id', $leadIds)->delete();
            }
        }

        // Also remove from viewings table if "Test Viewer" was recorded as a viewing inquiry
        if (Schema::hasTable('viewings')) {
            DB::table('viewings')
                ->where('name', 'Test Viewer')
                ->orWhere('name', 'like', '%Test Viewer%')
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed for removed test record
    }
};
