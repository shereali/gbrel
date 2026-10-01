<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lead CRM: who owns a lead, when to follow up, a timeline of everything done with it,
 * and site visits (the existing viewings table) linked to the lead they came from.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->timestamp('next_follow_up_at')->nullable()->index();
            $table->timestamp('last_contacted_at')->nullable();
            $table->string('lost_reason', 200)->nullable();
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type', 30)->index();
            $table->text('body')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
            $table->index(['lead_id', 'occurred_at']);
        });

        Schema::table('viewings', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('visit_type', 30)->default('Site visit');
            $table->timestamp('scheduled_at')->nullable();
            $table->text('outcome_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('viewings', function (Blueprint $table) {
            $table->dropIndex(['lead_id']);
            $table->dropColumn(['lead_id', 'assigned_to', 'visit_type', 'scheduled_at', 'outcome_notes']);
        });
        Schema::dropIfExists('lead_activities');
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['next_follow_up_at']);
            $table->dropColumn(['assigned_to', 'next_follow_up_at', 'last_contacted_at', 'lost_reason']);
        });
    }
};
