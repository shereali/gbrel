<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations to update CTA label and property pricing.
     */
    public function up(): void
    {
        // Already applied in production. It used to delete every lead and listing and re-run the seeder;
        // fresh installs now get the launch listings from DatabaseSeeder instead.
        Setting::setVal('property_cta_label', 'ক্রয় তথ্য ও সাইট ভিজিট');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
