<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Leads that arrive from Meta (Facebook / Instagram) Lead Ad instant forms.
 * meta_lead_id is unique so a webhook retry or a backfill never creates the same lead twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('meta_lead_id', 40)->nullable()->unique();
            $table->string('meta_form_id', 40)->nullable()->index();
            $table->string('meta_form_name')->nullable();
            $table->string('meta_ad_id', 40)->nullable();
            $table->string('meta_campaign_id', 40)->nullable()->index();
            $table->string('meta_platform', 20)->nullable();
            $table->json('meta_answers')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropUnique(['meta_lead_id']);
            $table->dropIndex(['meta_form_id']);
            $table->dropIndex(['meta_campaign_id']);
            $table->dropColumn(['meta_lead_id', 'meta_form_id', 'meta_form_name', 'meta_ad_id', 'meta_campaign_id', 'meta_platform', 'meta_answers']);
        });
    }
};
