<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations to update the default CTA label.
     */
    public function up(): void
    {
        Setting::setVal('property_cta_label', 'বিস্তারিত জানুন ও সাইট ভিজিট');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
