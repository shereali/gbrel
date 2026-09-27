<?php

use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations to update CTA label and property pricing.
     */
    public function up(): void
    {
        Setting::setVal('property_cta_label', 'ক্রয় তথ্য ও সাইট ভিজিট');

        // Re-run property seeder to update the 4 properties with corrected price units & details
        (new DatabaseSeeder)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
