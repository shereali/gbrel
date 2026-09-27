<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to purge old dummy properties and seed the 4 verified properties from PDFs.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('property_documents')) {
            DB::table('property_documents')->truncate();
        }
        if (Schema::hasTable('saved_properties')) {
            DB::table('saved_properties')->truncate();
        }
        if (Schema::hasTable('brochures')) {
            DB::table('brochures')->truncate();
        }
        if (Schema::hasTable('viewings')) {
            DB::table('viewings')->truncate();
        }
        if (Schema::hasTable('leads')) {
            DB::table('leads')->truncate();
        }
        if (Schema::hasTable('financial_transactions')) {
            DB::table('financial_transactions')->truncate();
        }
        if (Schema::hasTable('properties')) {
            DB::table('properties')->truncate();
        }
        if (Schema::hasTable('agents')) {
            DB::table('agents')->truncate();
        }

        Schema::enableForeignKeyConstraints();

        // Run the DatabaseSeeder which will seed the 4 PDF properties and 3 representatives
        $seeder = new \Database\Seeders\DatabaseSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
