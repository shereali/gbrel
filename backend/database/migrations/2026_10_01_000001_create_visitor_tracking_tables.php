<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Visitor journey tracking (only for visitors who accepted cookies).
 *
 * visitor_sessions : one row per visit (30 minutes without activity starts a new one), with the ad source
 * visitor_events   : every step inside a visit (page view, scroll, CTA click, survey step, lead...)
 * lead_drafts      : name/phone a visitor confirmed with consent before finishing the survey
 * leads            : get visitor_id + session_id so a saved lead links back to its journey
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 40)->unique();
            $table->string('visitor_id', 40)->index();
            $table->timestamp('started_at')->index();
            $table->timestamp('last_seen_at')->nullable();

            $table->string('utm_source', 100)->nullable()->index();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 255)->nullable()->index();
            $table->string('utm_content', 255)->nullable();
            $table->string('utm_term', 255)->nullable();
            $table->string('fb_campaign_id', 30)->nullable();
            $table->string('fb_adset_id', 30)->nullable();
            $table->string('fb_ad_id', 30)->nullable()->index();
            $table->string('fbclid', 255)->nullable();
            $table->string('fbc', 255)->nullable();
            $table->string('fbp', 255)->nullable();
            $table->text('landing_url')->nullable();
            $table->text('referrer')->nullable();

            $table->string('device', 20)->nullable();
            $table->string('browser', 60)->nullable();
            $table->boolean('in_facebook_app')->default(false);
            $table->string('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('ip_hash', 64)->nullable();

            $table->unsignedInteger('events_count')->default(0);
            $table->unsignedTinyInteger('max_scroll')->default(0);
            $table->unsignedInteger('seconds_active')->default(0);
            $table->string('furthest_step', 60)->nullable();
            $table->unsignedBigInteger('lead_id')->nullable()->index();
        });

        Schema::create('visitor_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 40)->index();
            $table->string('visitor_id', 40)->index();
            $table->string('event', 40)->index();
            $table->unsignedBigInteger('property_id')->nullable()->index();
            $table->string('label', 191)->nullable();
            $table->string('value', 191)->nullable();
            $table->unsignedSmallInteger('step')->nullable();
            $table->json('meta')->nullable();
            $table->string('path', 255)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['session_id', 'occurred_at']);
        });

        Schema::create('lead_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_id', 40)->index();
            $table->string('session_id', 40)->index();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->string('name', 120)->nullable();
            $table->string('phone', 20)->nullable()->index();
            $table->json('answers')->nullable();
            $table->unsignedSmallInteger('last_step')->nullable();
            $table->timestamp('consent_at');
            $table->unsignedBigInteger('converted_lead_id')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamps();
            $table->unique(['visitor_id', 'property_id']);
        });

        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'visitor_id')) {
                $table->string('visitor_id', 40)->nullable()->index();
            }
            if (! Schema::hasColumn('leads', 'session_id')) {
                $table->string('session_id', 40)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['visitor_id']);
            $table->dropIndex(['session_id']);
            $table->dropColumn(['visitor_id', 'session_id']);
        });
        Schema::dropIfExists('lead_drafts');
        Schema::dropIfExists('visitor_events');
        Schema::dropIfExists('visitor_sessions');
    }
};
