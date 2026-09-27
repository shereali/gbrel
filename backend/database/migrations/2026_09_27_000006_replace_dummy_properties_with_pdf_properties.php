<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Already applied in production. It used to delete every lead and listing and re-run the seeder;
     * fresh installs now get the launch listings from DatabaseSeeder instead.
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
